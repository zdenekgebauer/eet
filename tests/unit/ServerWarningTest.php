<?php
namespace ZdenekGebauer\Eet;

class ServerWarningTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	}

	protected function tearDown() {
	}

	public function testGetMessage() {
		$warning = new ServerWarning(0);
		$this->assertEquals(0, $warning->getCode());
		$this->assertEquals('undefined warning code:0', $warning->getMessage());

		$warning = new ServerWarning(ServerWarning::BKP_MISMATCH);
		$this->assertEquals(ServerWarning::BKP_MISMATCH, $warning->getCode());
		$this->assertEquals('Vraceny BKP nesouhlasi s odeslanym BKP', $warning->getMessage());

		$warning = new ServerWarning(ServerWarning::DIC_MISMATCH);
		$this->assertEquals(ServerWarning::DIC_MISMATCH, $warning->getCode());
		$this->assertEquals('DIC poplatnika v datove zprave se neshoduje s DIC v certifikatu', $warning->getMessage());

		$warning = new ServerWarning(ServerWarning::DIC_INVALID_FORMAT);
		$this->assertEquals(ServerWarning::DIC_INVALID_FORMAT, $warning->getCode());
		$this->assertEquals('Chybny format DIC poverujiciho poplatnika', $warning->getMessage());

		$warning = new ServerWarning(ServerWarning::DIC_INVALID_PKP);
		$this->assertEquals(ServerWarning::DIC_INVALID_PKP, $warning->getCode());
		$this->assertEquals('Chybna hodnota PKP', $warning->getMessage());

		$warning = new ServerWarning(ServerWarning::DATE_TOO_NEW);
		$this->assertEquals(ServerWarning::DATE_TOO_NEW, $warning->getCode());
		$this->assertEquals('Datum a cas prijeti trzby je novejsi nez datum a cas prijeti zpravy', $warning->getMessage());

		$warning = new ServerWarning(ServerWarning::DATE_TOO_OLD);
		$this->assertEquals(ServerWarning::DATE_TOO_OLD, $warning->getCode());
		$this->assertEquals('Datum a cas prijeti trzby je vyrazne v minulosti', $warning->getMessage());
	}
}
