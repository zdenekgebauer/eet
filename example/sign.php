<?php
/**
 * příklad podepsání účtenky bez odeslání na EET server
 */

namespace ZdenekGebauer\Eet;

require_once  '../vendor/autoload.php';

$config = new Config(
	dirname(__DIR__) . '/src/soap/EETServiceSOAP_Playground.wsdl', // nefunguje s WSDL ze SOAP serveru
	dirname(__DIR__) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12', 'eet'
);

$connector = new Connector($config);

$receipt = new Receipt();
$receipt
	->setPrvniZaslani(true)
	->setOvereni(false) // true na playgroundu vyhazuje ServerException::PROCESS_VERIFICATION_ERROR
	->setDicPoplatnika('CZ1212121218')
	->setIdProvozovny('273')
	->setIdPokladny('1')
	->setPoradoveCislo('1')
	->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
	->setCelkovaTrzba(1000.00);

try {
	$connector->sign($receipt);
	echo 'PKP:', $receipt->getPkpString(), '<br />';
	echo 'BKP:', $receipt->getBkp(), '<br />';
} catch (\Exception $exception) {
	echo 'Exception:', $exception->getCode(), '-', $exception->getMessage(), '<br />';
}
