<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\setup;

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
	 * @param EiPresetMode|null $eiPresetMode
	 * @param EiPresetProp[] $unassignedEiPresetPropsMap key must be property name.
	 */
	function __construct(private readonly EiType $eiType, private readonly ?EiPresetMode $eiPresetMode,
			private array $unassignedEiPresetPropsMap) {
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
