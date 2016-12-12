<?php

namespace ZdenekGebauer\Eet;

class ProductionTest extends \PHPUnit_Framework_TestCase {

	public function __construct() {
		parent::__construct();
		$this->config = new Config(
			dirname(dirname(__DIR__)) . '/src/soap/EETServiceSOAP_Playground.wsdl',
			dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12',
			'eet'
		);
		$this->config->setConnectionTimeout(10)
			->setTraceEnabled(true)
			->setTimezone('Europe/Prague');

		$this->connector = new Connector($this->config);
	}

	protected function setUp() {
	}

	protected function tearDown() {
	}

	public function testSendInvalidSign() {
		$config = new Config(
			dirname(dirname(__DIR__)) . '/src/soap/EETServiceSOAP_Production.wsdl',
			dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12',
			'eet'
		);
		$config->setTimezone('Europe/Prague');

		$connector = new Connector($config);

		$receipt = new Receipt();
		$receipt
			->setPrvniZaslani(true)
			->setOvereni(true)
			->setDicPoplatnika('CZ1212121218')
			->setIdProvozovny('273')
			->setIdPokladny('1')
			->setPoradoveCislo('1')
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($this->config->getTimezone())))
			->setCelkovaTrzba(1000.00);
		try {
			$connector->send($receipt);
			$this->fail('expected exception');
		} catch (ServerException $exception) {
			$this->assertEquals(ServerException::INVALID_SIGNATURE, $exception->getCode());
			$this->assertEquals('Neplatny podpis SOAP zpravy', $exception->getMessage());
		}

		$this->assertEquals(array(), $connector->getServerWarnings());
		$this->assertGreaterThan(0, $connector->getLastRequestDuration());
	}
}