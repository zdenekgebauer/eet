<?php
namespace ZdenekGebauer\Eet;

class CertificateTest extends \PHPUnit_Framework_TestCase {

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

	public function testOpencertificateSuccess() {
		$config = new Config($this->wsdl, $this->cert, $this->password);
		$certificate = new Certificate($config);

		$pemCert = '-----BEGIN CERTIFICATE-----
MIIEmDCCA4CgAwIBAgIEVjaXMDANBgkqhkiG9w0BAQsFADB3MRIwEAYKCZImiZPy
LGQBGRYCQ1oxQzBBBgNVBAoMOsSMZXNrw6EgUmVwdWJsaWthIOKAkyBHZW5lcsOh
bG7DrSBmaW5hbsSNbsOtIMWZZWRpdGVsc3R2w60xHDAaBgNVBAMTE0VFVCBDQSAx
IFBsYXlncm91bmQwHhcNMTYwOTMwMDkwMjQ0WhcNMTkwOTMwMDkwMjQ0WjBDMRIw
EAYKCZImiZPyLGQBGRYCQ1oxFTATBgNVBAMTDENaMTIxMjEyMTIxODEWMBQGA1UE
DRMNZnl6aWNrYSBvc29iYTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEB
AIY6O5tIJmB+GFrZsIAjZukigWqFWm9JR6y+O23BFSFIsNxLXlSr+o8PMlvc2xn3
25R2mlBmfWGSeNVC+VzNj0lUnXt5xkFAQTzUAGy5Vw395w0gjffP0a0aEOJbpP/j
/NKVwMmcNCgmR7TMdrHFY+iVlUeBXayShQUi5iwkioSJ7lVHnZpo/vPEuGK1P9ZC
br60HwyRrsgmE+ZPtlBUi5zPtNj0tFVRQ6p31fgDBFNKS+vRL8p9pBI0u2x+Ju64
j2LBm4wbyX1tlgqNV0Eg/B+aHIi5LJNfX4AKEVQggso4ymD6RLP84UsYR03gRxGR
VdrVx45LW0zslUg2M/OFFl8CAwEAAaOCAV4wggFaMAkGA1UdEwQCMAAwHQYDVR0O
BBYEFJPcMF6yIt00KetjxoNkR6lS1Sc7MB8GA1UdIwQYMBaAFHwwdqzM1ofR7Mkf
4nAILONf3gwHMA4GA1UdDwEB/wQEAwIGwDBjBgNVHSAEXDBaMFgGCmCGSAFlAwIB
MAEwSjBIBggrBgEFBQcCAjA8DDpUZW50byBjZXJ0aWZpa8OhdCBieWwgdnlkw6Fu
IHBvdXplIHBybyB0ZXN0b3ZhY8OtIMO6xI1lbHkuMIGXBgNVHR8EgY8wgYwwgYmg
gYaggYOGKWh0dHA6Ly9jcmwuY2ExLXBnLmVldC5jei9lZXRjYTFwZy9hbGwuY3Js
hipodHRwOi8vY3JsMi5jYTEtcGcuZWV0LmN6L2VldGNhMXBnL2FsbC5jcmyGKmh0
dHA6Ly9jcmwzLmNhMS1wZy5lZXQuY3ovZWV0Y2ExcGcvYWxsLmNybDANBgkqhkiG
9w0BAQsFAAOCAQEAOd3TksJlO4Cq6BfuAoWUqJP28p10f11W60X2TZ0LLEIeJHvl
Z2to6Pht8Pf50ZE4XPKyJclUDhT4dEoR0JcCiFZci8Oei35p6PAZ/dFEXBLHylMO
5JOY5JNwhUJNkhE2oSoCDBWpZ+tF6sPPeQv+dR9Zcj6vy767D0XGz6zyrxB3Lb1t
03SO+pGac/1C7dc3rOkBkqxz7b7dVRl7hT31ct/TTSMBBvPqStiUNF375nKb1pRT
SZtj5jt8m8UHChmu6bWyFGYLqil9XFHr3xeIGK8hRb4pPdjMEOY6HULZwImPg3Sn
P8fInbXA47hWoHb7pGwpdE5Jybveo6ae8HNx4w==
-----END CERTIFICATE-----
';
		$this->assertEquals($pemCert, $certificate->getCertificate());

		$pemKey = '-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCGOjubSCZgfhha
2bCAI2bpIoFqhVpvSUesvjttwRUhSLDcS15Uq/qPDzJb3NsZ99uUdppQZn1hknjV
QvlczY9JVJ17ecZBQEE81ABsuVcN/ecNII33z9GtGhDiW6T/4/zSlcDJnDQoJke0
zHaxxWPolZVHgV2skoUFIuYsJIqEie5VR52aaP7zxLhitT/WQm6+tB8Mka7IJhPm
T7ZQVIucz7TY9LRVUUOqd9X4AwRTSkvr0S/KfaQSNLtsfibuuI9iwZuMG8l9bZYK
jVdBIPwfmhyIuSyTX1+AChFUIILKOMpg+kSz/OFLGEdN4EcRkVXa1ceOS1tM7JVI
NjPzhRZfAgMBAAECggEAC2dnKQo4OHp+FznnhPt9AtGrrSEnTc0sCcEfy3NcGDfU
zuKYYRk7cGwaGzPoaYxlW3e4JJyQinmnSncmOHw+cDyAJL1z2pda85APeS1F+Cpa
NwNNDIDbj5lnVdPzcMot8LZIatialc6qyPGWJARhOKF8zVZwXvkT1Bvi8l0oZ5ow
4O1xhEzshkQYX2RwRthrOlIs196v5eI1QQ0e/+ferEODXKbyLsh5GjCHenibQj7Z
LarAnaZ86VzjbB6Nh9/rBd/K9bkturYBcRgiTSVO1XfLuJuV+cY5HA3SRuRKRdgm
b7QlA+e+kQJDfdywHshn6C5f6jqNHGlDgPlgab+6AQKBgQDABT/p3S2jBJ5A9XX8
vKeyoypASfenaXVmIQ3koBHf6qYVGx+dkNXHBtUs2EItUy5spiglk8r+AQWY4xDA
98XPI6bolLo+21kj6CMsWS/B4XM7qh9Cib6I5UHdwMh93bB1UN29sGY2HpSxZukd
IGmMvT3zZ1OMzG9+sV5O73/yPQKBgQCy82rmaumqgAqTOG2fRVeyaxsyKQ9fb4NL
C8TCYH8O0PVpTBIzMZoFWCh4EQlna8g4cZ4VmPIpwIy1P8X3UwUCL+T1p74Si9bU
Yfl1sqB+KT/V0QYeBWOl8r0+ZLJyzYZL388RrasnF+DNzfIbcAccNw0oWrd4HOKc
AOr1DlAAywKBgFGDqP8xWodClZ/D0+OHfrUx2OTTwaM6/JBvZcNxREVHClwZWJF+
A5JqzyIrZ+Rv1FxhKNfS5rBvZJ3jfqA8TqfBXcCMKog2e5/nks7nyYNHnrBsZrrL
WKwqjoyBo1rzOk6DFq7I7Ir67mpk4n7v3H7Xcy4Z5fj2bDpfN0bRGwKNAoGBAI7p
GqwdIbLKQqfD3rfdduXD55otdFtxANdD9MSOr6mzcum+mKJNsIUoHFmWsX3oc6Ow
COGSnYJ+hWCSJ5UWtd9DRIRyi7bf+pbuD3zRRJ68boBhR5NeFnCG5F8Zp/FK9T9O
411o5lB4H038dKc41lTQGi/qEq9X0hloGjvOTFH1AoGAO2W17QNKIobp4YuMqQts
0c0DeEtvC79n8LIPs70vOZ8219XASDxO9xkqPz3QO5+gn5AvBTE10pKfzOTDlDVJ
ut4YdsJVQSpK8xE/xJAhbVZzkM7G5EP6BMDAWwjvr4d//kkamfMer+34IsAEBvh9
fyiOKYRDFd2/pjV4JHWrs0o=
-----END PRIVATE KEY-----
';
		$this->assertEquals($pemKey, $certificate->getPrivateKey());
	}

	public function testOpencertificateFail() {
		$config = new Config($this->wsdl, $this->cert, 'invalid');
		try {
			$certificate = new Certificate($config);
			$this->fail('expected exception');
		} catch (ClientException $exception) {
			$this->assertEquals(ClientException::CANNOT_OPEN_CERTIFICATE, $exception->getCode());
			$this->assertEquals('Cannot open PKCS#12 certificate', $exception->getMessage());
		}
	}
}
