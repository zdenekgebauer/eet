<?php
/**
 * příklad odeslání účtenky na testovací EET server
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
	//->setDicPoverujicihoPoplatnika('') // nepovinný údaj
	->setIdProvozovny('273')
	->setIdPokladny('1')
	->setPoradoveCislo('1')
	->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
	->setCelkovaTrzba(1000.00)
	/*->setZakladNepodlehajiciDph(0) //nepovinné údaje, význam je třeba zjistit z dokumentace na etrzby
	->setZakladDan1(0)
	->setDan1(0)
	->setZakladDan2(0)
	->setDan2(0)
	->setZakladDan3(0)
	->setDan3(0)
	->setCestovniSluzba(0)
	->setPouziteZbozi1(0)
	->setPouziteZbozi2(0)
	->setPouziteZbozi3(0)
	->setUrcenoCerpaniZuctovani(0)
	->setCerpaniZuctovani(0)
	->setRezim(Receipt::REZIM_TRZBY_BEZNY)*/
;

try {
	$fik = $connector->send($receipt);
	echo 'FIK:', $fik, '<br />';
	echo 'Varování:<br />';
	foreach ($connector->getServerWarnings() as $warning) {
		echo $warning->getCode(), ':', $warning->getMessage(), '<br />';
	}
} catch (\Exception $exception) {
	var_dump($exception);
}
// debug
echo 'Request duration:', $connector->getLastRequestDuration(), ' ms<br />';
echo 'Request:<br />', nl2br($connector->getLastRequestHeaders()), '<br />';
echo htmlspecialchars($connector->getLastRequest()), '<br />';
echo 'Response:<br />', nl2br($connector->getLastResponseHeaders()), '<br />';
echo htmlspecialchars($connector->getLastResponse()), '<br />';
