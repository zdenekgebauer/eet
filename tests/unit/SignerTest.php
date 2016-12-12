<?php
namespace ZdenekGebauer\Eet;

class SignerTest extends \PHPUnit_Framework_TestCase {

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

	public function testSignXml() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$signer = new Signer(new Certificate($config));

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://fs.mfcr.cz/eet/schema/v3"><SOAP-ENV:Body><ns1:Trzba><ns1:Hlavicka uuid_zpravy="3f02c8e6-4a77-46c0-baf6-6d06ac5c00fb" dat_odesl="2016-12-05T21:06:12+01:00" prvni_zaslani="true" overeni="false"/><ns1:Data dic_popl="CZ1212121218" id_provoz="273" id_pokl="1" porad_cis="1" dat_trzby="2016-11-19T16:45:30+01:00" celk_trzba="1000.00" zakl_nepodl_dph="0.00" zakl_dan1="0.00" dan1="0.00" zakl_dan2="0.00" dan2="0.00" zakl_dan3="0.00" dan3="0.00" cest_sluz="0.00" pouzit_zboz1="0.00" pouzit_zboz2="0.00" pouzit_zboz3="0.00" urceno_cerp_zuct="0.00" cerp_zuct="0.00" rezim="0"/><ns1:KontrolniKody><ns1:pkp digest="SHA256" cipher="RSA2048" encoding="base64">EEZUbRykuIiJLoS6dzZ0cU55yGAPqHSMgpXkvXTsI0y8VkQs85l2ohPDcWpHuvcK/ORXdLAFGhUHheLcqoJ/usFugfB6K9YsZaCK82v5+dLKXqPGrzNyVrMDIPXhqLJsGMeYMy8fMKW3oFAFvgrie6Mu9ciJE0jkTJlypi3poxNTahdF7xGzpILBPwJU5SYIF+NaEfkgR5iuFmSR2RREZKHiD3eUNuhN73VitL70IC5duJ4LSbO74Nj3/0YYUuq741Irbm+TMWp+qy00mtf1V4gPj/peAh+FORBl6jtSy4CbcyTcpKBq61juB83tWQauF0me6kx3d4egrgbbUKjLvA==</ns1:pkp><ns1:bkp digest="SHA1" encoding="base16">da6cf2b0-06fb8a81-2522677e-60c63b8c-6a9eb723</ns1:bkp></ns1:KontrolniKody></ns1:Trzba></SOAP-ENV:Body></SOAP-ENV:Envelope>';

		$signedXml = $signer->signXml($xml);
		$this->assertContains('<wsse:Security', $signedXml);
		$this->assertContains('<wsse:BinarySecurityToken', $signedXml);
		$this->assertContains('<wsse:SecurityTokenReference', $signedXml);
	}
}
