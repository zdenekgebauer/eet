<?php
namespace ZdenekGebauer\Eet;

class SoapClientTest extends \PHPUnit_Framework_TestCase {

	private $wsdl = '';

	private $cert = '';

	private $password = '';

	protected function setUp() {
		$this->wsdl = dirname(dirname(__DIR__)) . '/src/soap/EETServiceSOAP_Playground.wsdl';
		$this->cert = dirname(dirname(__DIR__)) . '/src/cert/EET_CA1_Playground-CZ1212121218.p12';
		$this->password = 'eet';
	}

	protected function tearDown() {
	}

	public function testConstructor() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$certificate = new Certificate($config);
		//$signer = new Signer($certificate);
		$client = new SoapClient(
			$config->getWsdl(),
			array(
				'trace' => 1,
				'connection_timeout' => 10
			),
			new Signer($certificate)
		);
		$this->assertEquals(0, $client->getDuration());

	}
}
