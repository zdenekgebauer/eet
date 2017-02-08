<?php
/**
 * ZdenekGebauer\Eet\ServerException
 */

namespace ZdenekGebauer\Eet;

/**
 * exception caused by EET server
 */
class ServerException extends \Exception {

	const NO_RESPONSE = -100;

	const RESPONSE_TIMEOUT = -101;

	const CURL_CONNECTION_FAILED = -102;

	const CURL_EXCEPTION = -103;

	const TEMP_PROCESS_ERROR = -1;

	const PROCESS_VERIFICATION_ERROR = 0;

	const INVALID_XML_ENCODING = 2;

	const INVALID_XML_SCHEMA = 3;

	const INVALID_SIGNATURE = 4;

	const INVALID_BKP = 5;

	const INVALID_DIC = 6;

	const MESSAGE_TOO_BIG = 7;

	const PROCESS_ERROR = 8;

	/**
	 * construct the exception
	 * @param int $code
	 * @param string $message
	 * @param \Exception|null $previous
	 */
	public function __construct($code, $message = '', \Exception $previous = null) {
		$names = array(
			self::NO_RESPONSE => 'EET server did not respond',
			self::RESPONSE_TIMEOUT => 'EET server did not respond within specified time',
			self::CURL_CONNECTION_FAILED => 'Curl connection failed',
			self::TEMP_PROCESS_ERROR => 'Docasna technicka chyba zpracovani – odeslete prosim datovou zpravu pozdeji',
			self::PROCESS_VERIFICATION_ERROR => 'Datovou zpravu evidovane trzby v overovacim modu se podarilo zpracovat',
			self::INVALID_XML_ENCODING => 'Kodovani XML neni platne',
			self::INVALID_XML_SCHEMA => 'XML zprava nevyhovela kontrole XML schematu',
			self::INVALID_SIGNATURE => 'Neplatny podpis SOAP zpravy',
			self::INVALID_BKP => 'Neplatny kontrolni bezpecnostni kod poplatnika (BKP)',
			self::INVALID_DIC => 'DIC poplatnika ma chybnou strukturu',
			self::MESSAGE_TOO_BIG => 'Datova zprava je prilis velka',
			self::PROCESS_ERROR => 'Datova zprava nebyla zpracovana kvuli technicke chybe nebo chybe dat'
		);
		$exceptionMessage = isset($names[$code]) ? $names[$code]
			: ($message === '' ? 'Undefined error from server:' . $code : $message);
		parent::__construct($exceptionMessage, $code, $previous);
	}
}
