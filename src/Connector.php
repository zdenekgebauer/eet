<?php
/**
 * ZdenekGebauer\Eet\Connector
 */

namespace ZdenekGebauer\Eet;

use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * EET server connector
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Connector {

	/**
	 * @var Config configuration
	 */
	private $config;

	/**
	 * @var Certificate PKCS12 certificate
	 */
	private $certificate;

	/**
	 * @var SoapClient SOAP client
	 */
	private $soapClient = null;

	/**
	 * @var ServerWarning[] warnings from EET server
	 */
	private $serverWarnings = array();

	/**
	 * constructor
	 * @param Config $config
	 */
	public function __construct(Config $config) {
		$this->config = $config;
		$this->certificate = new Certificate($this->config);
	}

	/**
	 * send receipt to EET server
	 * returns FIK code
	 * @param Receipt $receipt
	 * @return string
	 * @throws ServerException
	 */
	public function send(Receipt $receipt) {
		$this->serverWarnings = array();
		$receipt
			->setUuid(self::getUuid())
			->setDatumOdeslani(new \DateTime('now', new \DateTimeZone($this->config->getTimezone())))
			->validate();
		$this->createSoapClient();
		try {
			/** @noinspection PhpUndefinedMethodInspection */
			$response = $this->soapClient->OdeslaniTrzby($this->createMessage($receipt));
			$this->parseResponse($response, $receipt);
		} catch (\Exception $exception) {
			if (stripos($exception->getMessage(), 'Error Fetching http headers') !== false
							&& ($this->soapClient->getDuration() / 1000) > $this->config->getResponseTimeout()
			) {
				throw new ServerException(ServerException::RESPONSE_TIMEOUT);
			}
			throw $exception;
		}
		return $response->Potvrzeni->fik;
	}

	/**
	 * set PKP and BKP codes on receipt
	 * @param Receipt $receipt
	 */
	public function sign(Receipt $receipt) {
		$this->serverWarnings = array();
		$receipt
			->setUuid(self::getUuid())
			->setDatumOdeslani(new \DateTime('now', new \DateTimeZone($this->config->getTimezone())))
			->validate();

		$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$objKey->loadKey($this->certificate->getPrivateKey());

		$signedData = array(
			$receipt->getDicPoplatnika(),
			$receipt->getIdProvozovny(),
			$receipt->getIdPokladny(),
			$receipt->getPoradoveCislo(),
			$receipt->getDatumTrzby()->format('c'),
			self::formatAmount($receipt->getCelkovaTrzba())
		);
		$receipt->setPkp($objKey->signData(join('|', $signedData)))
			->setBkp(wordwrap(substr(sha1($receipt->getPkp()), 0, 40), 8, '-', true));
	}

	/**
	 * UUID v4
	 * @return string
	 */
	private static function getUuid() {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * create SOAP client
	 */
	private function createSoapClient() {
		if ($this->soapClient === null) {
			$this->soapClient = new SoapClient(
				$this->config->getWsdl(),
				array(
					'trace' => ($this->config->isTraceEnabled() ? 1 : 0),
					'connection_timeout' => $this->config->getConnectionTimeout()
				),
				new Signer($this->certificate), $this->config->getResponseTimeout()
			);
		}
	}

	/**
	 * create message from receipt
	 * @param Receipt $receipt
	 * @return array
	 */
	private function createMessage(Receipt $receipt) {
		$head = array(
			'uuid_zpravy' => $receipt->getUuid(),
			'dat_odesl' => $receipt->getDatumOdeslani()->format('c'),
			'prvni_zaslani' => $receipt->isPrvniZaslani(),
			'overeni' => $receipt->isOvereni()
		);
		$this->sign($receipt);
		return array(
			'Hlavicka' => $head,
			'Data' => $this->prepareData($receipt),
			'KontrolniKody' => $this->prepareCodes($receipt)
		);
	}

	/**
	 * formats amount for message
	 * @param float $amount
	 * @return string
	 */
	private static function formatAmount($amount) {
		return number_format($amount, 2, '.', '');
	}

	/**
	 * prepares data part for message
	 * @param Receipt $receipt
	 * @return array
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	private function prepareData(Receipt $receipt) {
		$data = array('dic_popl' => $receipt->getDicPoplatnika());
		if ($receipt->getDicPoverujicihoPoplatnika() !== '') {
			$data['dic_poverujiciho'] = $receipt->getDicPoverujicihoPoplatnika();
		}
		$data['id_provoz'] = $receipt->getIdProvozovny();
		$data['id_pokl'] = $receipt->getIdPokladny();
		$data['porad_cis'] = $receipt->getPoradoveCislo();
		$data['dat_trzby'] = $receipt->getDatumTrzby()->format('c');
		$data['celk_trzba'] = self::formatAmount($receipt->getCelkovaTrzba());
		$data['zakl_nepodl_dph'] = self::formatAmount($receipt->getZakladNepodlehajiciDph());
		$data['zakl_dan1'] = self::formatAmount($receipt->getZakladDan1());
		$data['dan1'] = self::formatAmount($receipt->getDan1());
		$data['zakl_dan2'] = self::formatAmount($receipt->getZakladDan2());
		$data['dan2'] = self::formatAmount($receipt->getDan2());
		$data['zakl_dan3'] = self::formatAmount($receipt->getZakladDan3());
		$data['dan3'] = self::formatAmount($receipt->getDan3());
		$data['cest_sluz'] = self::formatAmount($receipt->getCestovniSluzba());
		$data['pouzit_zboz1'] = self::formatAmount($receipt->getPouziteZbozi1());
		$data['pouzit_zboz2'] = self::formatAmount($receipt->getPouziteZbozi2());
		$data['pouzit_zboz3'] = self::formatAmount($receipt->getPouziteZbozi3());
		$data['urceno_cerp_zuct'] = self::formatAmount($receipt->getUrcenoCerpaniZuctovani());
		$data['cerp_zuct'] = self::formatAmount($receipt->getCerpaniZuctovani());
		$data['rezim'] = $receipt->getRezim();
		return $data;
	}

	/**
	 * prepares signing codes for message
	 * @param Receipt $receipt
	 * @return array
	 */
	private function prepareCodes(Receipt $receipt) {
		return array(
			'pkp' => array(
				'_' => $receipt->getPkp(),
				'digest' => 'SHA256',
				'cipher' => 'RSA2048',
				'encoding' => 'base64'
			),
			'bkp' => array(
				'_' => $receipt->getBkp(),
				'digest' => 'SHA1',
				'encoding' => 'base16'
			)
		);
	}

	/**
	 * parse SOAP response into warnings, trace
	 * @param \stdClass|null $response
	 * @param Receipt $receipt
	 * @throws ServerException
	 */
	private function parseResponse($response, Receipt $receipt) {
		if ($response === null) {
			throw new ServerException(ServerException::NO_RESPONSE);
		}

		if (isset($response->Varovani)) {
			if ($response->Varovani instanceof \stdClass) {
				$response->Varovani = array($response->Varovani);
			}
			foreach ($response->Varovani as $warning) {
				$this->serverWarnings[] = new ServerWarning($warning->kod_varov);
			}
		}
		if (isset($response->Hlavicka->bkp) && $response->Hlavicka->bkp !== $receipt->getBkp()) {
			$this->serverWarnings[] = new ServerWarning(ServerWarning::BKP_MISMATCH);
		}
		if (isset($response->Chyba)) {
			throw new ServerException($response->Chyba->kod);
		}
	}

	/**
	 * warnings from EET server
	 * @return ServerWarning[]
	 */
	public function getServerWarnings() {
		return $this->serverWarnings;
	}

	/**
	 * duration of last request in milliseconds
	 * @return float
	 */
	public function getLastRequestDuration() {
		return $this->soapClient === null ? 0 : $this->soapClient->getDuration();
	}

	/**
	 * headers from the last request
	 * @return string
	 */
	public function getLastRequestHeaders() {
		return $this->soapClient === null ? '' : $this->soapClient->__getLastRequestHeaders();
	}

	/**
	 * XML with last request
	 * @return string
	 */
	public function getLastRequest() {
		return $this->soapClient === null ? '' : $this->soapClient->__getLastRequest();
	}

	/**
	 * headers from the last response
	 * @return string
	 */
	public function getLastResponseHeaders() {
		return $this->soapClient === null ? '' : $this->soapClient->__getLastResponseHeaders();
	}

	/**
	 * XML with last response
	 * @return string
	 */
	public function getLastResponse() {
		return $this->soapClient === null ? '' : $this->soapClient->__getLastResponse();
	}

	/**
	 * just for testing purposes
	 * @param \stdClass $client mock of class \SoapClient
	 */
	public function injectSoapClient($client) {
		$this->soapClient = $client;
	}
}
