<?php
/**
 * ZdenekGebauer\Eet\ClientException
 */

namespace ZdenekGebauer\Eet;

/**
 * exception caused by client
 */
class ClientException extends \Exception {

	/**
	 * problem with PKCS#12 certificate (probably invalid file or password)
	 */
	const CANNOT_OPEN_CERTIFICATE = 1;

	const INVALID_DIC = 2;

	const INVALID_ID_PROVOZOVNY = 3;

	const INVALID_ID_POKLADNY = 4;

	const INVALID_PORADOVE_CISLO = 5;
}
