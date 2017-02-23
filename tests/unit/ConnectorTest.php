<?php

namespace ZdenekGebauer\Eet;

class ConnectorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var string
	 */
	private $wsdl = '';

	/**
	 * @var string
	 */
	private $cert = '';

	/**
	 * @var string
	 */
	private $password = '';

	protected function setUp() {
		$this->wsdl = dirname(dirname(__DIR__)) . '/src/soap/EETServiceSOAP_Playground.wsdl';
		$this->cert = dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12';
		$this->password = 'eet';
	}

	protected function tearDown() {
	}

	public function testSendSuccess() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$connector = new Connector($config);

		$soapClient = $this->getMockFromWsdl($this->wsdl);
		$result = new \stdClass();
		$result->Hlavicka = new \stdClass();
		$result->Hlavicka->uuid_zpravy = 'c45de92c-fa2e-4ade-a531-7a80d54b05b0';
		$result->Hlavicka->bkp = 'da6cf2b0-06fb8a81-2522677e-60c63b8c-6a9eb723';
		$result->Hlavicka->dat_prij = '2016-12-03T11:32:08+01:00';
		$result->Potvrzeni = new \stdClass();
		$result->Potvrzeni->fik = '0c5346d0-4600-42a2-85bd-58070a2e5ad8-ff';
		$result->Potvrzeni->test = true;

		$soapClient->expects($this->any())
			->method('OdeslaniTrzby')
			->will($this->returnValue($result));
		$soapClient->expects($this->any())
			->method('__getLastRequestHeaders')
			->will($this->returnValue("POST /eet/services/EETServiceSOAP/v3 HTTP/1.1"));
		$soapClient->expects($this->any())
			->method('__getLastRequest')
			->will($this->returnValue('<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope>request</SOAP-ENV:Envelope>'));
		$soapClient->expects($this->any())
			->method('__getLastResponseHeaders')
			->will($this->returnValue("HTTP/1.1 200 OK"));
		$soapClient->expects($this->any())
			->method('__getLastResponse')
			->will($this->returnValue('<?xml version="1.0" encoding="UTF-8"?> <soapenv:Envelope>response</soapenv:Envelope>'));

		$connector->injectSoapClient($soapClient);

		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)
			->setDicPoplatnika('CZ1212121218')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
			->setCelkovaTrzba(1000.00)
			->setZakladNepodlehajiciDph(1)
			->setZakladDan1(100)
			->setDan1(121)
			->setZakladDan2(100)
			->setDan2(15)
			->setZakladDan3(100)
			->setDan3(15)
			->setCestovniSluzba(11)
			->setPouziteZbozi1(10)
			->setPouziteZbozi2(20)
			->setPouziteZbozi3(30)
			->setUrcenoCerpaniZuctovani(12)
			->setCerpaniZuctovani(13)
			->setRezim(Receipt::REZIM_TRZBY_BEZNY);

		$fik = $connector->send($receipt);
		$this->assertRegExp('/(\S){8}-(\S){4}-(\S){4}-(\S){4}-(\S){12}-ff/', $fik);
		$this->assertEquals(array(), $connector->getServerWarnings());
		$this->assertEquals(0, $connector->getLastRequestDuration());
		$this->assertContains('POST /eet/services/EETServiceSOAP/v3 HTTP/1.1', $connector->getLastRequestHeaders());
		$this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $connector->getLastRequest());
		$this->assertContains('<SOAP-ENV:Envelope>request', $connector->getLastRequest());
		$this->assertContains('HTTP/1.1 200 OK', $connector->getLastResponseHeaders());
		$this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $connector->getLastResponse());
		$this->assertContains('<soapenv:Envelope>response', $connector->getLastResponse());
	}

	public function testSendFail() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$connector = new Connector($config);

		$soapClient = $this->getMockFromWsdl($this->wsdl);
		$result = new \stdClass();
		$result->Hlavicka = new \stdClass();
		$result->Hlavicka->uuid_zpravy = 'c45de92c-fa2e-4ade-a531-7a80d54b05b0';
		$result->Hlavicka->bkp = 'da6cf2b0-06fb8a81-2522677e-60c63b8c-6a9eb723';
		$result->Hlavicka->dat_prij = '2016-12-03T11:32:08+01:00';
		$result->Chyba = new \stdClass();
		$result->Chyba->kod = 3;

		$soapClient->expects($this->any())
			->method('OdeslaniTrzby')
			->will($this->returnValue($result));
		$connector->injectSoapClient($soapClient);

		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)
			->setDicPoplatnika('CZ1212121218')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
			->setCelkovaTrzba(1000.00);

		try {
			$connector->send($receipt);
			$this->fail('expected ServerException');
		} catch (ServerException $exception) {
			$this->assertEquals(ServerException::INVALID_XML_SCHEMA, $exception->getCode());
			$this->assertEquals('XML zprava nevyhovela kontrole XML schematu', $exception->getMessage());
		}
		$this->assertEquals(array(), $connector->getServerWarnings());
		$this->assertEquals(0, $connector->getLastRequestDuration());
	}

	public function testSendWarning() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$connector = new Connector($config);

		$soapClient = $this->getMockFromWsdl($this->wsdl);
		$result = new \stdClass();
		$result->Hlavicka = new \stdClass();
		$result->Hlavicka->uuid_zpravy = 'c45de92c-fa2e-4ade-a531-7a80d54b05b0';
		$result->Hlavicka->bkp = 'da6cf2b0-06fb8a81-2522677e-60c63b8c-6a9eb723';
		$result->Hlavicka->dat_prij = '2016-12-03T11:32:08+01:00';
		$result->Potvrzeni = new \stdClass();
		$result->Potvrzeni->fik = '0c5346d0-4600-42a2-85bd-58070a2e5ad8-ff';
		$result->Potvrzeni->test = true;
		$result->Varovani = new \stdClass();
		$result->Varovani->kod_varov = ServerWarning::DIC_MISMATCH;

		$soapClient->expects($this->any())
			->method('OdeslaniTrzby')
			->will($this->returnValue($result));
		$connector->injectSoapClient($soapClient);

		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)
			->setDicPoplatnika('CZ1212121218')
			->setDicPoverujicihoPoplatnika('CZ000000191')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
			->setCelkovaTrzba(1000.00);
		$fik = $connector->send($receipt);
		$this->assertRegExp('/(\S){8}-(\S){4}-(\S){4}-(\S){4}-(\S){12}-ff/', $fik);
		$warnings = $connector->getServerWarnings();
		$this->assertEquals(1, count($connector->getServerWarnings()));
		$this->assertTrue((bool)array_filter($warnings, function ($warning) {
			return $warning->getCode() === ServerWarning::DIC_MISMATCH;
		}));
		$this->assertEquals(0, $connector->getLastRequestDuration());
	}

	public function testSendMultipleWarnings() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$connector = new Connector($config);

		// fake SOAP client
		$soapClient = $this->getMockFromWsdl($this->wsdl);
		$result = new \stdClass();
		$result->Hlavicka = new \stdClass();
		$result->Hlavicka->uuid_zpravy = 'c45de92c-fa2e-4ade-a531-7a80d54b05b0';
		$result->Hlavicka->bkp = 'da6cf2b0-06fb8a81-2522677e-60c63b8c-invalid';
		$result->Hlavicka->dat_prij = '2016-12-03T11:32:08+01:00';
		$result->Potvrzeni = new \stdClass();
		$result->Potvrzeni->fik = '0c5346d0-4600-42a2-85bd-58070a2e5ad8-ff';
		$result->Potvrzeni->test = true;
		$result->Varovani[0] = new \stdClass();
		$result->Varovani[0]->kod_varov = ServerWarning::DIC_MISMATCH;
		$result->Varovani[1] = new \stdClass();
		$result->Varovani[1]->kod_varov = ServerWarning::DIC_INVALID_FORMAT;

		$soapClient->expects($this->any())
			->method('OdeslaniTrzby')
			->will($this->returnValue($result));
		$connector->injectSoapClient($soapClient);

		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)
			->setDicPoplatnika('CZ1212121218')
			->setDicPoverujicihoPoplatnika('CZ000000191')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
			->setCelkovaTrzba(1000.00);
		$fik = $connector->send($receipt);
		$this->assertRegExp('/(\S){8}-(\S){4}-(\S){4}-(\S){4}-(\S){12}-ff/', $fik);
		$warnings = $connector->getServerWarnings();
		$this->assertEquals(3, count($connector->getServerWarnings()));
		$this->assertTrue((bool)array_filter($warnings, function ($warning) {
			return $warning->getCode() === ServerWarning::DIC_MISMATCH;
		}));
		$this->assertTrue((bool)array_filter($warnings, function ($warning) {
			return $warning->getCode() === ServerWarning::DIC_INVALID_FORMAT;
		}));
		$this->assertTrue((bool)array_filter($warnings, function ($warning) {
			return $warning->getCode() === ServerWarning::BKP_MISMATCH;
		}));
		$this->assertEquals(0, $connector->getLastRequestDuration());
	}

	public function testSendEmptyResponse() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$connector = new Connector($config);

		$soapClient = $this->getMockFromWsdl($this->wsdl);

		$soapClient->expects($this->any())
			->method('OdeslaniTrzby')
			->will($this->returnValue(null));
		$connector->injectSoapClient($soapClient);
		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(false)// true na playgroundu vyhazuje ServerException::PROCESS_VERIFICATION_ERROR
			->setDicPoplatnika('CZ1212121218')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
			->setCelkovaTrzba(1000.00);

		try {
			$connector->send($receipt);
			$this->fail('expected ServerException');
		} catch (ServerException $exception) {
			$this->assertEquals(ServerException::NO_RESPONSE, $exception->getCode());
			$this->assertEquals('EET server did not respond', $exception->getMessage());
		}
		$this->assertEquals(array(), $connector->getServerWarnings());
		$this->assertEquals(0, $connector->getLastRequestDuration());
	}
}

/*class stdClass#203 (2) {
  public $Hlavicka =>
  class stdClass#457 (3) {
    public $uuid_zpravy =>
    string(36) "c45de92c-fa2e-4ade-a531-7a80d54b05b0"
    public $bkp =>
    string(44) "da6cf2b0-06fb8a81-2522677e-60c63b8c-6a9eb723"
    public $dat_prij =>
    string(25) "2016-12-03T11:32:08+01:00"
  }
  public $Potvrzeni =>
  class stdClass#471 (2) {
    public $fik =>
    string(39) "0c5346d0-4600-42a2-85bd-58070a2e5ad8-ff"
    public $test =>
    bool(true)
  }
}*/

/*D:\projekty\test\eet_connector\src\Connector.php:75:
class stdClass#202 (2) {
  public $Hlavicka =>
  class stdClass#453 (2) {
    public $uuid_zpravy =>
    string(36) "8e13cdc0-cfed-4366-a9a3-e9c01fe7c31e"
    public $dat_odmit =>
    string(25) "2016-12-03T16:22:42+01:00"
  }
  public $Chyba =>
  class stdClass#472 (2) {
    public $kod =>
    int(3)
    public $test =>
    bool(true)
  }
}*/

/*class stdClass#203 (3) {
  public $Hlavicka =>
  class stdClass#457 (3) {
    public $uuid_zpravy =>
    string(36) "64e187b8-c46c-4527-baa1-513dd04d55d3"
    public $bkp =>
    string(44) "49690848-ed37a69d-4e82df7f-a95e2e7a-6e26361b"
    public $dat_prij =>
    string(25) "2016-12-03T17:50:33+01:00"
  }
  public $Potvrzeni =>
  class stdClass#473 (2) {
    public $fik =>
    string(39) "2d342a89-1ac5-4442-8f91-cb5a6f852897-ff"
    public $test =>
    bool(true)
  }
  public $Varovani =>
  class stdClass#454 (1) {
    public $kod_varov =>
    int(1)
  }
}

D:\projekty\test\eet_connector\src\Connector.php:75:
class stdClass#203 (3) {
  public $Hlavicka =>
  class stdClass#457 (3) {
    public $uuid_zpravy =>
    string(36) "6545bcea-1dfa-4118-a239-51ae477a965c"
    public $bkp =>
    string(44) "49690848-ed37a69d-4e82df7f-a95e2e7a-6e26361b"
    public $dat_prij =>
    string(25) "2016-12-03T17:54:43+01:00"
  }
  public $Potvrzeni =>
  class stdClass#473 (2) {
    public $fik =>
    string(39) "2096a691-affa-44fa-978f-e04b98aab6ab-ff"
    public $test =>
    bool(true)
  }
  public $Varovani =>
  array(2) {
    [0] =>
    class stdClass#454 (1) {
      public $kod_varov =>
      int(1)
    }
    [1] =>
    class stdClass#458 (1) {
      public $kod_varov =>
      int(2)
    }
  }
}

*/