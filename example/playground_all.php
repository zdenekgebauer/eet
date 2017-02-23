<?php
/**
 * příklad odeslání účtenky na testovací EET server se všemi údaji
 */

namespace ZdenekGebauer\Eet;

require_once  '../vendor/autoload.php';

$config = new Config(
	dirname(__DIR__) . '/src/soap/EETServiceSOAP_Playground.wsdl', // nefunguje s WSDL ze SOAP serveru
	dirname(__DIR__) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12', 'eet'
);
$config->setConnectionTimeout(3) // 3s na připojení k EET
	->setResponseTimeout(3) // 3s na zpracování requestu
	->setTraceEnabled(true) // false když nejsou třeba ladící informace ze SOAP clienta
	->setTimezone('Europe/Prague'); // časová zóna, ve které se uvádějí datumy

$connector = new Connector($config);

$receipt = new Receipt();
$receipt
	->setPrvniZaslani(true)
	->setOvereni(false) // true na playgroundu vyhazuje ServerException::PROCESS_VERIFICATION_ERROR
	->setDicPoplatnika('CZ1212121218')
	->setDicPoverujicihoPoplatnika('CZ1212121200')
	->setIdProvozovny('273')
	->setIdPokladny('1')
	->setPoradoveCislo('1')
	->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
	->setCelkovaTrzba(1000.00)
	->setZakladNepodlehajiciDph(100.20)
	->setZakladDan1(100)
	->setDan1(21)
	->setZakladDan2(100)
	->setDan2(15)
	->setZakladDan3(100)
	->setDan3(10)
	->setCestovniSluzba(4)
	->setPouziteZbozi1(10)
	->setPouziteZbozi2(20)
	->setPouziteZbozi3(30)
	->setUrcenoCerpaniZuctovani(5)
	->setCerpaniZuctovani(6)
	->setRezim(Receipt::REZIM_TRZBY_ZJEDNODUSENY)
;

try {
	$fik = $connector->send($receipt);
	echo 'FIK:', $fik, '<br />';
	echo 'Varování:<br />';
	foreach ($connector->getServerWarnings() as $warning) {
		echo $warning->getCode(), ':', $warning->getMessage(), '<br />';
	}
} catch (\Exception $exception) {
	echo 'Exception:', $exception->getCode(), '-', $exception->getMessage(), '<br />';
}
// debug
echo 'Request duration:', $connector->getLastRequestDuration(), ' ms<br />';
echo 'Request:<br />', nl2br($connector->getLastRequestHeaders()), '<br />';
echo htmlspecialchars($connector->getLastRequest()), '<br />';
echo 'Response:<br />', nl2br($connector->getLastResponseHeaders()), '<br />';
echo htmlspecialchars($connector->getLastResponse()), '<br />';
