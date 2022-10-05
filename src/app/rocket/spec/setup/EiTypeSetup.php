<?php

namespace rocket\spec;

use rocket\ei\EiType;
use n2n\util\type\ArgUtils;
use ReflectionClass;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\ReflectionContext;
use n2n\reflection\attribute\AttributeSet;
use rocket\ei\component\prop\EiPropNature;
use rocket\ei\component\command\EiCmdNature;
use rocket\ei\component\modificator\EiModNature;

class EiTypeSetup {

	/**
	 * @param EiType $eiType
	 * @param EiPresetProp[] $unassignedEiPresetPropsMap key must be property name.
	 */
	function __construct(private EiType $eiType, private ?EiPresetMode $eiPresetMode,
			private readonly $unassignedEiPresetPropsMap) {
		ArgUtils::valArray($unassignedEiPresetPropsMap, EiPresetProp::class);
	}

	/**
	 * @return EntityModel
	 */
	function getEntityModel() {
		return $this->eiType->getEntityModel();
	}

	/**
	 * @return ReflectionClass
	 */
	function getClass() {
		return $this->getEntityModel()->getClass();
	}

	/**
	 * @return EiPresetMode|null
	 */
	function getEiPresetMode() {
		return $this->eiPresetMode;
	}

	/**
	 * @return EiPresetProp[]
	 */
	function getUnassignedEiPresetProps() {
		return $this->unassignedEiPresetPropsMap;
	}

	/**
	 * @return AttributeSet
	 */
	function getAttributeSet() {
		return ReflectionContext::getAttributeSet($this->getClass());
	}

	function addEiPropNature(?string $propertyName, EiPropNature $eiPropNature, ?string $id = null) {
		if ($propertyName !== null) {
			unset($this->unassignedEiPresetPropsMap[$propertyName]);
		}

		$this->eiType->getEiMask()->getEiPropCollection()->add($eiPropNature, $id ?? $propertyName);
	}

	function addEiCmdNature(EiCmdNature $eiCmdNature, ?string $id = null) {
		$this->eiType->getEiMask()->getEiCmdCollection()->add($eiCmdNature, $id);
	}

	function addEiModNature(EiModNature $eiModNature, ?string $id = null) {
		$this->eiType->getEiMask()->getEiModCollection()->add($eiModNature, $id);
	}
}
