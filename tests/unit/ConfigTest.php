<?php

namespace ZdenekGebauer\Eet;

class ConfigTest extends \PHPUnit_Framework_TestCase {

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

	public function testContructor() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$config->setTraceEnabled(false)
			->setTimezone('UTC')
			->setConnectionTimeout(5)
			->setUseCurl(true);

		$this->assertEquals($this->wsdl, $config->getWsdl());
		$this->assertEquals($this->cert, $config->getCertificate());
		$this->assertEquals($this->password, $config->getPassword());
		$this->assertFalse($config->isTraceEnabled());
		$this->assertEquals('UTC', $config->getTimezone());
		$this->assertEquals(5, $config->getConnectionTimeout());
		$this->assertTrue($config->isUseCurl());
	}
}
