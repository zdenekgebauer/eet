<?php

namespace ZdenekGebauer\Eet;

class PlaygroundCurlTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Connector
	 */
	private $connector;

	/**
	 * @var Config
	 */
	private $config;

	public function __construct() {
		parent::__construct();
		$this->config = new Config(
			dirname(dirname(__DIR__)) . '/src/soap/EETServiceSOAP_Playground.wsdl',
			dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12',
			'eet'
		);
		$this->config->setConnectionTimeout(10)
			->setTraceEnabled(true)
			->setTimezone('Europe/Prague')
			->setUseCurl(true)
			->setCurlVerifySslPeer(false); // false if SSL certificate fail

		$this->connector = new Connector($this->config);
	}

	protected function setUp() {
	}

	protected function tearDown() {
	}

	public function testSendSuccess() {
		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)// true na playgroundu vyhazuje ServerException::PROCESS_VERIFICATION_ERROR
			->setDicPoplatnika('CZ1212121218')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($this->config->getTimezone())))
			->setCelkovaTrzba(1000.00);

		$fik = $this->connector->send($receipt);
		$this->assertRegExp('/(\S){8}-(\S){4}-(\S){4}-(\S){4}-(\S){12}-ff/', $fik);
		$this->assertEquals(array(), $this->connector->getServerWarnings());
		$this->assertGreaterThan(0, $this->connector->getLastRequestDuration());
		$this->assertContains('Host: pg.eet.cz', $this->connector->getLastRequestHeaders());
		$this->assertContains('SOAPAction: http://fs.mfcr.cz/eet/OdeslaniTrzby', $this->connector->getLastRequestHeaders());
		$this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $this->connector->getLastRequest());
		$this->assertContains('<SOAP-ENV:Envelope', $this->connector->getLastRequest());
		$this->assertContains('<wsse:Security', $this->connector->getLastRequest());
		$this->assertContains('<wsse:BinarySecurityToken', $this->connector->getLastRequest());
		$this->assertContains('<ds:SignedInfo>', $this->connector->getLastRequest());

		$this->assertContains('HTTP/1.1 200 OK', $this->connector->getLastResponseHeaders());
		$this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $this->connector->getLastResponse());
		$this->assertContains(
			'<soapenv:Envelope xmlns:eet="http://fs.mfcr.cz/eet/schema/v3"', $this->connector->getLastResponse()
		);
		$this->assertContains('<SignedInfo>', $this->connector->getLastResponse());
		$this->assertContains('<SignatureValue>', $this->connector->getLastResponse());
		$this->assertContains('<eet:Potvrzeni fik="' . $fik . '" test="true"/>', $this->connector->getLastResponse());
	}

	public function testSendWarning() {
		$this->config->setCurlVerifySslPeer(false);
		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)
			->setDicPoplatnika('CZ121212121')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2015-11-19T16:45:30', new \DateTimeZone($this->config->getTimezone())))
			->setCelkovaTrzba(1000.00);

		$fik = $this->connector->send($receipt);
		$this->assertRegExp('/(\S){8}-(\S){4}-(\S){4}-(\S){4}-(\S){12}-ff/', $fik);

		$warnings = $this->connector->getServerWarnings();
		$this->assertEquals(2, count($this->connector->getServerWarnings()));
		$this->assertTrue((bool)array_filter($warnings, function ($warning) {
			return $warning->getCode() === ServerWarning::DIC_MISMATCH;
		}));
		$this->assertTrue((bool)array_filter($warnings, function ($warning) {
			return $warning->getCode() === ServerWarning::DATE_TOO_OLD;
		}));

		$this->assertGreaterThan(0, $this->connector->getLastRequestDuration());
		$this->assertContains('Host: pg.eet.cz', $this->connector->getLastRequestHeaders());
		$this->assertContains('SOAPAction: http://fs.mfcr.cz/eet/OdeslaniTrzby', $this->connector->getLastRequestHeaders());
		$this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $this->connector->getLastRequest());
		$this->assertContains('<SOAP-ENV:Envelope', $this->connector->getLastRequest());
		$this->assertContains('<wsse:Security', $this->connector->getLastRequest());
		$this->assertContains('<wsse:BinarySecurityToken', $this->connector->getLastRequest());
		$this->assertContains('<ds:SignedInfo>', $this->connector->getLastRequest());

		$this->assertContains('HTTP/1.1 200 OK', $this->connector->getLastResponseHeaders());
		$this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $this->connector->getLastResponse());
		$this->assertContains(
			'<soapenv:Envelope xmlns:eet="http://fs.mfcr.cz/eet/schema/v3"', $this->connector->getLastResponse()
		);
		$this->assertContains('<SignedInfo>', $this->connector->getLastResponse());
		$this->assertContains('<SignatureValue>', $this->connector->getLastResponse());
		$this->assertContains('<eet:Potvrzeni fik="' . $fik . '" test="true"/>', $this->connector->getLastResponse());
	}
}