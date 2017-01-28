<?php

namespace ZdenekGebauer\Eet;

class SignTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	}

	protected function tearDown() {
	}

	public function testSignReceiptInvalidSign() {
		$config = new Config(
			dirname(dirname(__DIR__)) . '/src/soap/EETServiceSOAP_Playground.wsdl',
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
			->setDatumTrzby(new \DateTime('2016-11-19T16:45:30', new \DateTimeZone($config->getTimezone())))
			->setCelkovaTrzba(1000.00);

		$connector->sign($receipt);
		$this->assertEquals('EEZUbRykuIiJLoS6dzZ0cU55yGAPqHSMgpXkvXTsI0y8VkQs85l2ohPDcWpHuvcK/ORXdLAFGhUHheLcqoJ/usFugfB6K9YsZaCK82v5+dLKXqPGrzNyVrMDIPXhqLJsGMeYMy8fMKW3oFAFvgrie6Mu9ciJE0jkTJlypi3poxNTahdF7xGzpILBPwJU5SYIF+NaEfkgR5iuFmSR2RREZKHiD3eUNuhN73VitL70IC5duJ4LSbO74Nj3/0YYUuq741Irbm+TMWp+qy00mtf1V4gPj/peAh+FORBl6jtSy4CbcyTcpKBq61juB83tWQauF0me6kx3d4egrgbbUKjLvA==', $receipt->getPkpString());
		$this->assertEquals('da6cf2b0-06fb8a81-2522677e-60c63b8c-6a9eb723', $receipt->getBkp());
	}
}