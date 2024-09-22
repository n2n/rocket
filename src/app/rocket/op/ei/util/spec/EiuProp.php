<?php
namespace rocket\op\ei\util\spec;

use rocket\op\ei\EiPropPath;
use n2n\l10n\N2nLocale;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\component\InvalidEiConfigurationException;
use Throwable;
use n2n\reflection\property\PropertyAccessException;
use rocket\op\ei\util\entry\EiuObject;
use n2n\util\type\TypeConstraint;
use n2n\l10n\Lstr;

class EiuProp {
	private $eiPropPath;
	private $eiuMask;
	private $eiuAnalyst;

	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiuMask $eiuMask
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiPropPath $eiPropPath, EiuMask $eiuMask, EiuAnalyst $eiuAnalyst) {
		$this->eiPropPath = $eiPropPath;
		$this->eiuMask = $eiuMask;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\op\ei\EiPropPath
	 */
	public function getPath() {
		return $this->eiPropPath;
	}
	
	/**
	 * @return \rocket\op\ei\component\prop\EiProp
	 */
	public function getEiProp() {
		return $this->eiuMask->getEiMask()->getEiPropCollection()->getByPath($this->eiPropPath);
	}
	
	/**
	 * @param N2nLocale|null $n2nLocale
	 * @return string
	 */
	public function getLabel(N2nLocale $n2nLocale = null) {
		return $this->eiuMask->getPropLabel($this->eiPropPath, $n2nLocale);
	}

	function getLabelLstr(): Lstr {
		return $this->eiuMask->getPropLabelLstr($this->eiPropPath);
	}
	
//	/**
//	 * @param N2nLocale|null $n2nLocale
//	 * @return string
//	 */
//	public function getPluralLabel(N2nLocale $n2nLocale = null) {
//		return $this->eiuMask->getPropPluralLabel($this->eiPropPath, $n2nLocale);
//	}
	
	/**
	 * @param N2nLocale|null $n2nLocale
	 * @return string
	 */
	public function getHelpText(N2nLocale $n2nLocale = null) {
		return $this->eiuMask->getPropHelpText($this->eiPropPath, $n2nLocale);
	}
	
	/**
	 * @return boolean
	 */
	public function isGeneric() {
		return $this->eiuMask->engine()->containsGenericEiProperty($this->eiPropPath);
	}
	
	/**
	 * @param string $entityAlias
	 * @throws \rocket\op\ei\manage\generic\UnknownGenericEiPropertyException if {@see self::isGeneric()} returns false
	 */
	public function createGenericCriteriaItem(string $entityAlias) {
		return $this->eiuMask->engine()->getGenericEiProperty($this->eiPropPath)->createCriteriaItem(CrIt::p($entityAlias));
	}
	
	/**
	 * @param mixed $eiEntryArg See {@see EiuAnalyst::buildEiEntryFromEiArg()}
	 * @param bool $ignoreAccessRestriction
	 * @return mixed
	 */
	public function createGenericEntityValue($eiEntryArg, bool $ignoreAccessRestriction = false) {
		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg);
		
		return $this->eiuMask->engine()->getGenericEiProperty($this->eiPropPath)
				->eiFieldValueToEntityValue($eiEntry->getValue($this->eiPropPath, $ignoreAccessRestriction));
	}

	/**
	 * @param string|null $message
	 * @param Throwable|null $previous
	 * @return mixed
	 */
	function createConfigException(string $message = null, Throwable $previous = null) {
		throw new InvalidEiConfigurationException('Invalid configuration for EiProp ' . $this->getEiProp()
				. ' Reason: ' . ($message ?? $previous?->getMessage() ?? 'unknown'), $previous);
	}

	function getNativeReadTypeConstraint(): ?TypeConstraint {
		return $this->getEiProp()->getNature()->getNativeAccessProxy()?->getGetterConstraint();
	}

	function isNativeReadable(): bool {
		return (bool) $this->getEiProp()->getNature()->getNativeAccessProxy()?->isReadable();
	}

	/**
	 * @throws PropertyAccessException
	 */
	function readNativeValue(EiuObject $eiuObject = null) {
		return ($eiuObject ?? $this->eiuAnalyst->getEiuObject(true))->readNativeValue($this->getEiProp());
	}

	function isNativeWritable(): bool {
		return (bool) $this->getEiProp()->getNature()->getNativeAccessProxy()?->isWritable();
	}

	/**
	 * @throws PropertyAccessException
	 */
	function writeNativeValue(mixed $value, EiuObject $eiuObject = null): static {
		($eiuObject ?? $this->eiuAnalyst->getEiuObject(true))->writeNativeValue($value, $this->getEiProp());
		return $this;
	}
}