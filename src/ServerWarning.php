<?php
/**
 * ZdenekGebauer\Eet\ServerWarning
 */

namespace ZdenekGebauer\Eet;

/**
 * warning from EET server
 */
class ServerWarning {

	const BKP_MISMATCH = -1;

	const DIC_MISMATCH = 1;

	const DIC_INVALID_FORMAT = 2;

	const DIC_INVALID_PKP = 3;

	const DATE_TOO_NEW = 4;

	const DATE_TOO_OLD = 5;

	/**
	 * @var int warning code
	 */
	private $code = 0;

	/**
	 * constructor
	 * @param int $code
	 */
	public function __construct($code) {
		$this->code = (int)$code;
	}

	/**
	 * warning code
	 * codes greater than 0 comes from EET documentation
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * warning message
	 * @return string
	 */
	public function getMessage() {
		$messages = array(
			self::BKP_MISMATCH => 'Vraceny BKP nesouhlasi s odeslanym BKP',
			self::DIC_MISMATCH => 'DIC poplatnika v datove zprave se neshoduje s DIC v certifikatu',
			self::DIC_INVALID_FORMAT => 'Chybny format DIC poverujiciho poplatnika',
			self::DIC_INVALID_PKP => 'Chybna hodnota PKP',
			self::DATE_TOO_NEW => 'Datum a cas prijeti trzby je novejsi nez datum a cas prijeti zpravy',
			self::DATE_TOO_OLD => 'Datum a cas prijeti trzby je vyrazne v minulosti'
		);
		return isset($messages[$this->code]) ? $messages[$this->code] : 'undefined warning code:' . $this->code;
	}
}
