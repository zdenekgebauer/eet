<?php
/**
 * ZdenekGebauer\Eet\SoapClient
 */

namespace ZdenekGebauer\Eet;

/**
 * SOAP Client for EET
 */
class SoapClient extends \SoapClient {

	/**
	 * @var Signer creator of digitally signed XML
	 */
	private $signer;

	/**
	 * @var float duration of last request
	 */
	private $duration = 0;

	/**
	 * @var int timeout for the response from EET SOAP in seconds
	 */
	private $responseTimeout = 5;

	/**
	 * @var int timeout for the connection to the EET SOAP in seconds
	 */
	private $connectionTimeout = 3;

	/**
	 * @var bool true|false = use curl|native SOAP
	 */
	private $useCurl = true;

	/**
	 * @var string headers from last curl request
	 */
	private $lastRequestHeaders = '';

	/**
	 * @var string headers from last curl response
	 */
	private $lastResponseHeaders = '';

	/**
	 * constructor
	 * @param string $wsdl path to WSDL file
	 * @param array $options
	 * @param Signer $signer
	 * @param int $responseTimeout timeout for the response from EET SOAP in seconds, minimal 2
	 * @param bool $useCurl true/false = use curl/SOAP
	 * @link http://php.net/manual/en/soapclient.soapclient.php
	 */
	public function __construct($wsdl, array $options, Signer $signer, $responseTimeout = 2, $useCurl = false) {
		$this->signer = $signer;
		$responseTimeout = (int)$responseTimeout;
		$this->responseTimeout = ($responseTimeout > 2 ? $responseTimeout : 2);
		$this->connectionTimeout = (isset($options['connection_timeout']) && (int)$options['connection_timeout'] > 2
			? (int)$options['connection_timeout'] : 2);
		$this->useCurl = (bool)$useCurl;
		parent::__construct($wsdl, $options);
	}

	/**
	 * Performs a SOAP request
	 * @param string $request The XML SOAP request
	 * @param string $location The URL to request
	 * @param string $action The SOAP action
	 * @param int $version The SOAP version
	 * @param int $one_way [optional] If one_way is set to 1, this method returns nothing.
	 * @return string The XML SOAP response
	 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
	 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 */
	public function __doRequest($request, $location, $action, $version, $one_way = 0) {
		$request = $this->signer->signXml($request);
		$origTimeout = ini_get('default_socket_timeout');
		ini_set('default_socket_timeout', $this->responseTimeout);
		$time = microtime(true);
		if ($this->useCurl) {
			$response = $this->doRequestCurl($request, $location, $action, $version);
		} else {
			$response = parent::__doRequest($request, $location, $action, $version, $one_way);
		}
		$this->duration = (microtime(true) - $time) * 1000;
		ini_set('default_socket_timeout', $origTimeout);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->__last_request = $request;
		return $response;
	}

	/**
	 * duration of last request in milliseconds
	 * @return int
	 */
	public function getDuration() {
		return $this->duration;
	}

	/**
	 * Performs a SOAP request with curl
	 * @param string $request
	 * @param string $location
	 * @param string $action
	 * @param int $version
	 * @return string
	 * @throws ServerException
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	private function doRequestCurl($request, $location, $action, $version) {
		$curl = curl_init($location);
		if ($curl === false) {
			throw new ServerException('Curl failed', ServerException::CURL_CONNECTION_FAILED);
		}
		$headers = array(
			'Content-Type: text/xml; charset=utf-8',
			'SOAPAction: "' . $action . '"',
			'Content-Length: ' . strlen($request),
		);
		curl_setopt($curl, CURLOPT_VERBOSE, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_HEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->responseTimeout);
		curl_setopt(
			$curl, CURLOPT_HTTPHEADER, array(
				sprintf('Content-Type: %s', $version == 2 ? 'application/soap+xml' : 'text/xml'),
				sprintf('SOAPAction: %s', $action)
			)
		);

		$response = curl_exec($curl);
		$this->lastRequestHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);

		if (curl_errno($curl)) {
			$errorMessage = curl_error($curl);
			$errorNumber  = curl_errno($curl);
			curl_close($curl);
			throw new ServerException($errorNumber . ':' . $errorMessage, ServerException::CURL_EXCEPTION);
		}

		$headerLength = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$body = trim(substr($response, $headerLength));
		$this->lastResponseHeaders = substr($response, 0, $headerLength);
		curl_close($curl);
		return $body;
	}

	/**
	 * Returns the headers from the last request
	 * @return string
	 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
	 */
	public function __getLastRequestHeaders() {
		if ($this->useCurl) {
			return $this->lastRequestHeaders;
		}
		return parent::__getLastResponseHeaders();
	}

	/**
	 * Returns the headers from the last response
	 * @return string
	 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
	 */
	public function __getLastResponseHeaders() {
		if ($this->useCurl) {
			return $this->lastResponseHeaders;
		}
		return parent::__getLastResponseHeaders();
	}

}
