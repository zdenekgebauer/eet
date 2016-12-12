<?php
/**
 * ZdenekGebauer\Eet\Certificate
 */

namespace ZdenekGebauer\Eet;

/**
 * parse PKCS#12 certificate file
 */
class Certificate {

	/**
	 * @var string RSA private key
	 */
	private $privateKey = '';

	/**
	 * @var string certificate
	 */
	private $certificate = '';

	/**
	 * parse PKCS#12 certificate store
	 * @param Config $config
	 * @throws ClientException
	 */
	public function __construct(Config $config) {
		$certs = array();
		if (!openssl_pkcs12_read(file_get_contents($config->getCertificate()), $certs, $config->getPassword())) {
			throw new ClientException('Cannot open PKCS#12 certificate', ClientException::CANNOT_OPEN_CERTIFICATE);
		}
		$this->privateKey = $certs['pkey'];
		$this->certificate = $certs['cert'];
	}

	/**
	 * RSA private key
	 * @return string
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}

	/**
	 * RSA certificate
	 * @return string
	 */
	public function getCertificate() {
		return $this->certificate;
	}
}
