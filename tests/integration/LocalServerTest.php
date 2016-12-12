<?php

namespace ZdenekGebauer\Eet;

/**
 * requires proper location in tests/_data/*.wsdl
 * <soap:address location="http://localhost/tests/_data/500.php"/>
 */
class ConnectionErrorTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	}

	protected function tearDown() {
	}

	public function testSendNoResponse() {
		$config = new Config(
			dirname(__DIR__) . '/_data/EETServiceSOAP_noresponse.wsdl',
			dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12',
			'eet'
		);
		$config->setTimezone('Europe/Prague')
			->setResponseTimeout(3);

		$connector = new Connector($config);

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
			$this->fail('expected exception');
		} catch (ServerException $exception) {
			$this->assertEquals(ServerException::NO_RESPONSE, $exception->getCode());
			$this->assertEquals('EET server did not respond', $exception->getMessage());
		}
		$this->assertEquals(array(), $connector->getServerWarnings());
		$this->assertGreaterThan(0, $connector->getLastRequestDuration());
	}

	public function testSendError500() {
		$config = new Config(
			dirname(__DIR__) . '/_data/EETServiceSOAP_500.wsdl',
			dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12',
			'eet'
		);
		$config->setTimezone('Europe/Prague');

		$connector = new Connector($config);

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
			$this->fail('expected exception');
		} catch (\SoapFault $fault) {
			$this->assertEquals(0, $fault->getCode());
			$this->assertEquals('Internal Server Error', $fault->getMessage());
		}
		$this->assertEquals(array(), $connector->getServerWarnings());
		$this->assertGreaterThan(0, $connector->getLastRequestDuration());
	}
}
