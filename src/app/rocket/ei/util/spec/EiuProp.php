<?php
namespace rocket\ei\util\spec;

use rocket\ei\EiPropPath;
use n2n\l10n\N2nLocale;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\util\EiuAnalyst;

class EiuProp {
	private $eiPropPath;
	private $eiuMask;
	private $eiuAnalyst;
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiuEngine $eiuEngine
	 */
	public function __construct(EiPropPath $eiPropPath, EiuMask $eiuMask, EiuAnalyst $eiuAnalyst) {
		$this->eiPropPath = $eiPropPath;
		$this->eiuMask = $eiuMask;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param N2nLocale|null $n2nLocale
	 * @return string
	 */
	public function getLabel(N2nLocale $n2nLocale = null) {
		return $this->eiuMask->getPropLabel($this->eiPropPath);
	}
	
	/**
	 * @param N2nLocale|null $n2nLocale
	 * @return string
	 */
	public function getPluralLabel(N2nLocale $n2nLocale = null) {
		return $this->eiuMask->getPropPluralLabel($this->eiPropPath);
	}
	
	/**
	 * @return boolean
	 */
	public function isGeneric() {
		return $this->eiuEngine->containsGenericEiProperty($this->eiPropPath);
	}
	
	/**
	 * @param string $entityAlias
	 * @throws \rocket\ei\manage\generic\UnknownGenericEiPropertyException if {@see self::isGeneric()} returns false
	 */
	public function createGenericCriteriaItem(string $entityAlias) {
		return $this->eiuEngine->getGenericEiProperty($this->eiPropPath)->createCriteriaItem(CrIt::p($entityAlias));
	}
	
	/**
	 * @param mixed $eiEntryArg See {@see EiuAnalyst::buildEiEntryFromEiArg()}
	 * @param bool $ignoreAccessRestriction
	 * @return mixed
	 */
	public function createGenericEntityValue($eiEntryArg, bool $ignoreAccessRestriction = false) {
		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg);
		
		return $this->eiuEngine->getGenericEiProperty($this->eiPropPath)
				->eiFieldValueToEntityValue($eiEntry->getValue($this->eiPropPath, $ignoreAccessRestriction));
	}
}