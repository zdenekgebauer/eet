<?php
/**
 * ZdenekGebauer\Eet\Signer
 */
namespace ZdenekGebauer\Eet;

use RobRichards\WsePhp\WSSESoap;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * sign XML with certificate
 */
class Signer {

	/**
	 * @var Certificate certificate and private key
	 */
	private $certificate = '';

	/**
	 * constructor
	 * @param Certificate $certificate
	 */
	public function __construct(Certificate $certificate) {
		$this->certificate = $certificate;
	}

	/**
	 * returns digitally signed XML
	 * @param string $request XML
	 * @return string
	 */
	public function signXml($request) {

		$dom = new \DOMDocument('1.0');
		$dom->loadXML($request);

		$wsSoap = new WSSESoap($dom);
		$wsSoap->addTimestamp();

		$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$objKey->loadKey($this->certificate->getPrivateKey());
		$wsSoap->signSoapDoc($objKey, array('algorithm' => XMLSecurityDSig::SHA256));

		$token = $wsSoap->addBinaryToken($this->certificate->getCertificate());
		$wsSoap->attachTokentoSig($token);

		return $wsSoap->saveXML();
	}
}

