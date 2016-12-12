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
	 * constructor
	 * @param string $wsdl path to WSDL file
	 * @param array $options
	 * @param Signer $signer
	 * @param int $responseTimeout timeout for the response from EET SOAP in seconds, minimal 2
	 * @link http://php.net/manual/en/soapclient.soapclient.php
	 */
	public function __construct($wsdl, array $options, Signer $signer, $responseTimeout = 2) {
		$this->signer = $signer;
		$responseTimeout = (int)$responseTimeout;
		$this->responseTimeout = ($responseTimeout > 2 ? $responseTimeout : 2);
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
		$response = parent::__doRequest($request, $location, $action, $version, $one_way);
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
}
