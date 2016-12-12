<?php
/**
 * ZdenekGebauer\Eet\Receipt
 */

namespace ZdenekGebauer\Eet;

/**
 * účtenka odesílaná na EET
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Receipt {

	const REZIM_TRZBY_BEZNY = 0;

	const REZIM_TRZBY_ZJEDNODUSENY = 1;

	/**
	 * @var string UUID zprávy podle RFC 4122
	 */
	private $uuid = '';

	/**
	 * @var \DateTime Datum a čas odeslání zprávy
	 */
	private $datumOdeslani;

	/**
	 * @var bool První zaslání údajů o tržbě
	 */
	private $prvniZaslani = true;

	/**
	 * @var bool Příznak ověřovacího módu
	 */
	private $overeni = true;

	/**
	 * @var string DIČ poplatníka
	 */
	private $dicPoplatnika = '';

	/**
	 * @var string DIČ pověřujícího poplatníka
	 */
	private $dicPoverujicihoPoplatnika = '';

	/**
	 * @var string Označení provozovny
	 */
	private $idProvozovny = '';

	/**
	 * @var string Označení pokladního zařízení
	 */
	private $idPokladny = '';

	/**
	 * @var string Pořadové číslo účtenky
	 */
	private $poradoveCislo = '';

	/**
	 * @var \DateTime Datum a čas přijetí tržby
	 */
	private $datumTrzby;

	/**
	 * @var float Celková částka tržby
	 */
	private $celkovaTrzba = 0;

	/**
	 * @var float Celková částka plnění osvobozených od DPH, ostatních plnění
	 */
	private $zakladNepodlehajiciDph = 0;

	/**
	 * @var float Celkový základ daně se základní sazbou DPH
	 */
	private $zakladDan1 = 0;

	/**
	 * @var float Celková DPH se základní sazbou
	 */
	private $dan1 = 0;

	/**
	 * @var float Celkový základ daně s první sníženou sazbou DPH
	 */
	private $zakladDan2 = 0;

	/**
	 * @var float Celková DPH s první sníženou sazbou
	 */
	private $dan2 = 0;

	/**
	 * @var float Celkový základ daně s druhou sníženou
	 */
	private $zakladDan3 = 0;

	/**
	 * @var float Celková DPH s druhou sníženou sazbou
	 */
	private $dan3 = 0;

	/**
	 * @var float Celková částka v režimu DPH pro cestovní službu
	 */
	private $cestovniSluzba = 0;

	/**
	 * @var float Celková částka v režimu DPH pro prodej použitého zboží se základní sazbou
	 */
	private $pouziteZbozi1 = 0;

	/**
	 * @var float Celková částka v režimu DPH pro prodej použitého zboží s první sníženou sazbou
	 */
	private $pouziteZbozi2 = 0;

	/**
	 * @var float Celková částka v režimu DPH pro prodej použitého zboží s druhou sníženou sazbou
	 */
	private $pouziteZbozi3 = 0;

	/**
	 * @var float Celková částka plateb určená k následnému čerpání nebo zúčtování
	 */
	private $urcenoCerpaniZuctovani = 0;

	/**
	 * @var float Celková částka plateb, které jsou následným čerpáním nebo zúčtováním platby
	 */
	private $cerpaniZuctovani = 0;

	/**
	 * @var int Režim tržby
	 */
	private $rezim = self::REZIM_TRZBY_BEZNY;

	/**
	 * @var string Podpisový kód poplatníka
	 */
	private $pkp = '';

	/**
	 * @var string Bezpečnostní kód poplatníka
	 */
	private $bkp = '';

	/**
	 * validates filled data
	 * @throws ClientException
	 */
	public function validate() {
		if (preg_match('/^CZ[0-9]{8,10}$/', $this->getDicPoplatnika()) !== 1) {
			throw new ClientException('neplatne DIC poplatnika', ClientException::INVALID_DIC);
		}
		if ($this->getDicPoverujicihoPoplatnika() !== ''
						&& preg_match('/^CZ[0-9]{8,10}$/', $this->getDicPoverujicihoPoplatnika()) !== 1
		) {
			throw new ClientException('neplatne DIC poverujiciho poplatnika', ClientException::INVALID_DIC);
		}
		if (preg_match('/^[1-9][0-9]{0,5}$/', $this->getIdProvozovny()) !== 1) {
			throw new ClientException('neplatne ID provozovny', ClientException::INVALID_ID_PROVOZOVNY);
		}
		if (preg_match('/^[0-9a-zA-Z\.,:;\/#\-_ ]{1,20}$/', $this->getIdPokladny()) !== 1) {
			throw new ClientException('neplatne ID pokladniho zarizeni', ClientException::INVALID_ID_POKLADNY);
		}
		if (preg_match('/^[0-9a-zA-Z\.,:;\/#\-_ ]{1,25}$/', $this->getPoradoveCislo()) !== 1) {
			throw new ClientException('neplatne poradove cislo', ClientException::INVALID_PORADOVE_CISLO);
		}
	}

	/**
	 * UUID zprávy podle RFC 4122
	 * @return string
	 */
	public function getUuid() {
		return $this->uuid;
	}

	/**
	 * UUID zprávy podle RFC 4122
	 * @param string $uuid
	 * @return Receipt
	 */
	public function setUuid($uuid) {
		$this->uuid = (string)$uuid;
		return $this;
	}

	/**
	 * Datum a čas odeslání zprávy
	 * @return \DateTime
	 */
	public function getDatumOdeslani() {
		return $this->datumOdeslani;
	}

	/**
	 * Datum a čas odeslání zprávy
	 * @param \DateTime $datumOdeslani
	 * @return Receipt
	 */
	public function setDatumOdeslani(\DateTime $datumOdeslani) {
		$this->datumOdeslani = $datumOdeslani;
		return $this;
	}

	/**
	 * První zaslání údajů o tržbě
	 * @return boolean
	 */
	public function isPrvniZaslani() {
		return $this->prvniZaslani;
	}

	/**
	 * První zaslání údajů o tržbě
	 * @param boolean $prvniZaslani
	 * @return Receipt
	 */
	public function setPrvniZaslani($prvniZaslani) {
		$this->prvniZaslani = (bool)$prvniZaslani;
		return $this;
	}

	/**
	 * Příznak ověřovacího módu
	 * @return boolean
	 */
	public function isOvereni() {
		return $this->overeni;
	}

	/**
	 * Příznak ověřovacího módu
	 * @param boolean $overeni
	 * @return Receipt
	 */
	public function setOvereni($overeni) {
		$this->overeni = (bool)$overeni;
		return $this;
	}

	/**
	 * DIČ poplatníka
	 * @return string
	 */
	public function getDicPoplatnika() {
		return $this->dicPoplatnika;
	}

	/**
	 * DIČ poplatníka
	 * @param string $dic
	 * @return Receipt
	 */
	public function setDicPoplatnika($dic) {
		$this->dicPoplatnika = (string)$dic;
		return $this;
	}

	/**
	 * DIČ pověřujícího poplatníka
	 * @return string
	 */
	public function getDicPoverujicihoPoplatnika() {
		return $this->dicPoverujicihoPoplatnika;
	}

	/**
	 * DIČ pověřujícího poplatníka
	 * @param string $dic
	 * @return Receipt
	 */
	public function setDicPoverujicihoPoplatnika($dic) {
		$this->dicPoverujicihoPoplatnika = (string)$dic;
		return $this;
	}

	/**
	 * Označení provozovny
	 * @return string
	 */
	public function getIdProvozovny() {
		return $this->idProvozovny;
	}

	/**
	 * Označení provozovny
	 * @param string $idProvozovny
	 * @return Receipt
	 */
	public function setIdProvozovny($idProvozovny) {
		$this->idProvozovny = $idProvozovny;
		return $this;
	}

	/**
	 * Označení pokladního zařízení
	 * @return string
	 */
	public function getIdPokladny() {
		return $this->idPokladny;
	}

	/**
	 * Označení pokladního zařízení
	 * @param string $idPokladny
	 * @return Receipt
	 */
	public function setIdPokladny($idPokladny) {
		$this->idPokladny = $idPokladny;
		return $this;
	}

	/**
	 * Pořadové číslo účtenky
	 * @return string
	 */
	public function getPoradoveCislo() {
		return $this->poradoveCislo;
	}

	/**
	 * Pořadové číslo účtenky
	 * @param string $poradoveCislo
	 * @return Receipt
	 */
	public function setPoradoveCislo($poradoveCislo) {
		$this->poradoveCislo = $poradoveCislo;
		return $this;
	}

	/**
	 * Datum a čas přijetí tržby
	 * @return \DateTime
	 */
	public function getDatumTrzby() {
		return $this->datumTrzby;
	}

	/**
	 * Datum a čas přijetí tržby
	 * @param \DateTime $datumTrzby
	 * @return Receipt
	 */
	public function setDatumTrzby(\DateTime $datumTrzby) {
		$this->datumTrzby = $datumTrzby;
		return $this;
	}

	/**
	 * Celková částka tržby
	 * @return float
	 */
	public function getCelkovaTrzba() {
		return $this->celkovaTrzba;
	}

	/**
	 * Celková částka tržby
	 * @param float $celkovaTrzba
	 * @return Receipt
	 */
	public function setCelkovaTrzba($celkovaTrzba) {
		$this->celkovaTrzba = (float)$celkovaTrzba;
		return $this;
	}

	/**
	 * Celková částka plnění osvobozených od DPH, ostatních plnění
	 * @return float
	 */
	public function getZakladNepodlehajiciDph() {
		return $this->zakladNepodlehajiciDph;
	}

	/**
	 * Celková částka plnění osvobozených od DPH, ostatních plnění
	 * @param float $amount
	 * @return Receipt
	 */
	public function setZakladNepodlehajiciDph($amount) {
		$this->zakladNepodlehajiciDph = (float)$amount;
		return $this;
	}

	/**
	 * Celkový základ daně se základní sazbou DPH
	 * @return float
	 */
	public function getZakladDan1() {
		return $this->zakladDan1;
	}

	/**
	 * Celkový základ daně se základní sazbou DPH
	 * @param float $amount
	 * @return Receipt
	 */
	public function setZakladDan1($amount) {
		$this->zakladDan1 = (float)$amount;
		return $this;
	}

	/**
	 * Celková DPH se základní sazbou
	 * @return float
	 */
	public function getDan1() {
		return $this->dan1;
	}

	/**
	 * Celková DPH se základní sazbou
	 * @param float $amount
	 * @return Receipt
	 */
	public function setDan1($amount) {
		$this->dan1 = (float)$amount;
		return $this;
	}

	/**
	 * Celkový základ daně s první sníženou sazbou DPH
	 * @return float
	 */
	public function getZakladDan2() {
		return $this->zakladDan2;
	}

	/**
	 * Celkový základ daně s první sníženou sazbou DPH
	 * @param float $zakladDan2
	 * @return Receipt
	 */
	public function setZakladDan2($zakladDan2) {
		$this->zakladDan2 = $zakladDan2;
		return $this;
	}

	/**
	 * Celková DPH s první sníženou sazbou
	 * @return float
	 */
	public function getDan2() {
		return $this->dan2;
	}

	/**
	 * Celková DPH s první sníženou sazbou
	 * @param float $dan2
	 * @return Receipt
	 */
	public function setDan2($dan2) {
		$this->dan2 = (float)$dan2;
		return $this;
	}

	/**
	 * Celkový základ daně s druhou sníženou sazbou DPH
	 * @return float
	 */
	public function getZakladDan3() {
		return $this->zakladDan3;
	}

	/**
	 * Celkový základ daně s druhou sníženou sazbou DPH
	 * @param float $zakladDan3
	 * @return Receipt
	 */
	public function setZakladDan3($zakladDan3) {
		$this->zakladDan3 = $zakladDan3;
		return $this;
	}

	/**
	 * Celková DPH s druhou sníženou sazbou
	 * @return float
	 */
	public function getDan3() {
		return $this->dan3;
	}

	/**
	 * Celková DPH s druhou sníženou sazbou
	 * @param float $dan3
	 * @return Receipt
	 */
	public function setDan3($dan3) {
		$this->dan3 = (float)$dan3;
		return $this;
	}

	/**
	 * Celková částka v režimu DPH pro cestovní službu
	 * @return float
	 */
	public function getCestovniSluzba() {
		return $this->cestovniSluzba;
	}

	/**
	 * Celková částka v režimu DPH pro cestovní službu
	 * @param float $amount
	 * @return Receipt
	 */
	public function setCestovniSluzba($amount) {
		$this->cestovniSluzba = (float)$amount;
		return $this;
	}

	/**
	 * Celková částka v režimu DPH pro prodej použitého zboží se základní sazbou
	 * @return float
	 */
	public function getPouziteZbozi1() {
		return $this->pouziteZbozi1;
	}

	/**
	 * Celková částka v režimu DPH pro prodej použitého zboží se základní sazbou
	 * @param float $amount
	 * @return Receipt
	 */
	public function setPouziteZbozi1($amount) {
		$this->pouziteZbozi1 = (float)$amount;
		return $this;
	}

	/**
	 * Celková částka v režimu DPH pro prodej použitého zboží s první sníženou sazbou
	 * @return float
	 */
	public function getPouziteZbozi2() {
		return $this->pouziteZbozi2;
	}

	/**
	 * Celková částka v režimu DPH pro prodej použitého zboží s první sníženou sazbou
	 * @param float $amount
	 * @return Receipt
	 */
	public function setPouziteZbozi2($amount) {
		$this->pouziteZbozi2 = (float)$amount;
		return $this;
	}

	/**
	 * Celková částka v režimu DPH pro prodej použitého zboží s druhou sníženou sazbou
	 * @return float
	 */
	public function getPouziteZbozi3() {
		return $this->pouziteZbozi3;
	}

	/**
	 * Celková částka v režimu DPH pro prodej použitého zboží s druhou sníženou sazbou
	 * @param float $amount
	 * @return Receipt
	 */
	public function setPouziteZbozi3($amount) {
		$this->pouziteZbozi3 = (float)$amount;
		return $this;
	}

	/**
	 * Celková částka plateb určená k následnému čerpání nebo zúčtování
	 * @return float
	 */
	public function getUrcenoCerpaniZuctovani() {
		return $this->urcenoCerpaniZuctovani;
	}

	/**
	 * Celková částka plateb určená k následnému čerpání nebo zúčtování
	 * @param float $amount
	 * @return Receipt
	 */
	public function setUrcenoCerpaniZuctovani($amount) {
		$this->urcenoCerpaniZuctovani = (float)$amount;
		return $this;
	}

	/**
	 * Celková částka plateb, které jsou následným čerpáním nebo zúčtováním platby
	 * @return float
	 */
	public function getCerpaniZuctovani() {
		return $this->cerpaniZuctovani;
	}

	/**
	 * Celková částka plateb, které jsou následným čerpáním nebo zúčtováním platby
	 * @param float $amount
	 * @return Receipt
	 */
	public function setCerpaniZuctovani($amount) {
		$this->cerpaniZuctovani = (float)$amount;
		return $this;
	}

	/**
	 * Režim tržby
	 * @return int
	 */
	public function getRezim() {
		return $this->rezim;
	}

	/**
	 * Režim tržby
	 * @param int $rezim
	 * @return Receipt
	 */
	public function setRezim($rezim) {
		if (in_array($rezim, array(self::REZIM_TRZBY_BEZNY, self::REZIM_TRZBY_ZJEDNODUSENY))) {
			$this->rezim = $rezim;
		}
		return $this;
	}

	/**
	 * Podpisový kód poplatníka
	 * @return string
	 */
	public function getPkp() {
		return $this->pkp;
	}

	/**
	 * Podpisový kód poplatníka
	 * @param string $pkp
	 * @return Receipt
	 */
	public function setPkp($pkp) {
		$this->pkp = (string)$pkp;
		return $this;
	}

	/**
	 * Bezpečnostní kód poplatníka
	 * @return string
	 */
	public function getBkp() {
		return $this->bkp;
	}

	/**
	 * Bezpečnostní kód poplatníka
	 * @param string $bkp
	 * @return Receipt
	 */
	public function setBkp($bkp) {
		$this->bkp = (string)$bkp;
		return $this;
	}
}
