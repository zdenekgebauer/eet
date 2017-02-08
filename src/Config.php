<?php
/**
 * ZdenekGebauer\Eet\Config
 */

namespace ZdenekGebauer\Eet;

/**
 * connector configuration
 */
class Config {

	/**
	 * @var string to *.wsdl file
	 */
	private $wsdl = '';

	/**
	 * @var string to *.p12 file
	 */
	private $certificate = '';

	/**
	 * @var string password for certificate
	 */
	private $password = '';

	/**
	 * @var bool enabled tracing of request
	 */
	private $traceEnabled = false;

	/**
	 * @var int timeout for the connection to the EET SOAP in seconds
	 */
	private $connectionTimeout = 5;

	/**
	 * @var int timeout for the response from EET SOAP in seconds
	 */
	private $responseTimeout = 2;

	/**
	 * @var string timezone for dates sent to EET SOAP
	 */
	private $timezone = 'Europe/Prague';

	/**
	 * @var bool true|false = use curl|native SOAP
	 */
	private $useCurl = false;

	/**
	 * @var bool true|false = verify|don't verify peer's certificate
	 */
	private $curlVerifySslPeer = true;

	/**
	 * sets mandatory options
	 * $wsdl have to be local wsdl file (playground or production). Url of EET server does not work
	 * @param string $wsdl path to WSDL file
	 * @param string $certificate path to *.p12 file with certificate store
	 * @param string $password password for certificate
	 */
	public function __construct($wsdl, $certificate, $password) {
		$this->wsdl = $wsdl;
		$this->certificate = $certificate;
		$this->password = $password;
	}

	/**
	 * path to WSDL file
	 * @return string
	 */
	public function getWsdl() {
		return $this->wsdl;
	}

	/**
	 * path to *.p12 file with certificate
	 * @return string
	 */
	public function getCertificate() {
		return $this->certificate;
	}

	/**
	 * password for certificate
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * tracing of requests enabled?
	 * @return boolean
	 */
	public function isTraceEnabled() {
		return $this->traceEnabled;
	}

	/**
	 * enable or disabled tracing of requests. Enabled by default
	 * @param boolean $traceEnabled
	 * @return Config
	 */
	public function setTraceEnabled($traceEnabled) {
		$this->traceEnabled = (bool)$traceEnabled;
		return $this;
	}

	/**
	 * timeout for the connection to the EET SOAP in seconds
	 * @return int
	 */
	public function getConnectionTimeout() {
		return $this->connectionTimeout;
	}

	/**
	 * sets timeout for the connection to the EET SOAP in seconds. Minimal 1, default 5
	 * @param int $connectionTimeout
	 * @return Config
	 */
	public function setConnectionTimeout($connectionTimeout) {
		$connectionTimeout = (int)$connectionTimeout;
		if ($connectionTimeout > 0) {
			$this->connectionTimeout = $connectionTimeout;
		}
		return $this;
	}

	/**
	 * timeout for the response from EET server in seconds
	 * @return int
	 */
	public function getResponseTimeout() {
		return $this->responseTimeout;
	}

	/**
	 * sets timeout for the response from EET server in seconds. Minimal 2
	 * @param int $responseTimeout
	 * @return Config
	 */
	public function setResponseTimeout($responseTimeout) {
		$responseTimeout = (int)$responseTimeout;
		if ($responseTimeout > 2) {
			$this->responseTimeout = $responseTimeout;
		}
		return $this;
	}

	/**
	 * timezone for dates sent to EET SOAP
	 * @return string
	 */
	public function getTimezone() {
		return $this->timezone;
	}

	/**
	 * sets timezone for dates sent to EET SOAP. Default 'Europe/Prague'
	 * @param string $timezone
	 * @return Config
	 */
	public function setTimezone($timezone) {
		$this->timezone = (string)$timezone;
		return $this;
	}

	/**
	 * use curl or native SOAP
	 * @return bool
	 */
	public function isUseCurl() {
		return $this->useCurl;
	}

	/**
	 * sets connection via curl instead of native SOAP
	 * @param bool $useCurl
	 * @return Config
	 */
	public function setUseCurl($useCurl) {
		$this->useCurl = (bool)$useCurl;
		return $this;
	}

	/**
	 * verify or do not verify server certificate
	 * @return bool
	 */
	public function isCurlVerifySslPeer() {
		return $this->curlVerifySslPeer;
	}

	/**
	 * enable or disable verification of server certificate
	 * disable only when cannot fixed curl CA certificate on server
	 * @param bool $curlVerifySslPeer
	 * @return Config
	 */
	public function setCurlVerifySslPeer($curlVerifySslPeer) {
		$this->curlVerifySslPeer = (bool)$curlVerifySslPeer;
		return $this;
	}



}
