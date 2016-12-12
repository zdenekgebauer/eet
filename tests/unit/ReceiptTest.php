<?php
namespace ZdenekGebauer\Eet;

class ReceiptTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	}

	protected function tearDown() {
	}

	public function testValidate() {
		$receipt = new Receipt();

		try {
			$receipt->validate();
			$this->fail('expected exception');
		} catch (ClientException $exception) {
			$this->assertEquals(ClientException::INVALID_DIC, $exception->getCode());
			$this->assertEquals('neplatne DIC poplatnika', $exception->getMessage());
		}

		$receipt->setDicPoplatnika('CZ12345678')
			->setDicPoverujicihoPoplatnika('invalid');
		try {
			$receipt->validate();
			$this->fail('expected exception');
		} catch (ClientException $exception) {
			$this->assertEquals(ClientException::INVALID_DIC, $exception->getCode());
			$this->assertEquals('neplatne DIC poverujiciho poplatnika', $exception->getMessage());
		}

		$receipt->setDicPoverujicihoPoplatnika('')
			->setIdProvozovny('invalid');
		try {
			$receipt->validate();
			$this->fail('expected exception');
		} catch (ClientException $exception) {
			$this->assertEquals(ClientException::INVALID_ID_PROVOZOVNY, $exception->getCode());
			$this->assertEquals('neplatne ID provozovny', $exception->getMessage());
		}

		$receipt->setIdProvozovny('123')
			->setIdPokladny('+invalid');
		try {
			$receipt->validate();
			$this->fail('expected exception');
		} catch (ClientException $exception) {
			$this->assertEquals(ClientException::INVALID_ID_POKLADNY, $exception->getCode());
			$this->assertEquals('neplatne ID pokladniho zarizeni', $exception->getMessage());
		}
		$receipt->setIdPokladny('valid')
			->setPoradoveCislo('+invalid');
		try {
			$receipt->validate();
			$this->fail('expected exception');
		} catch (ClientException $exception) {
			$this->assertEquals(ClientException::INVALID_PORADOVE_CISLO, $exception->getCode());
			$this->assertEquals('neplatne poradove cislo', $exception->getMessage());
		}
		$receipt->setPoradoveCislo('1');
		$receipt->validate();
	}
}
