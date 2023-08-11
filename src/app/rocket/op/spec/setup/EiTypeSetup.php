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
namespace rocket\op\spec\setup;

use rocket\op\ei\EiType;
use n2n\util\type\ArgUtils;
use ReflectionClass;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\ReflectionContext;
use n2n\reflection\attribute\AttributeSet;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\component\modificator\EiModNature;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\StringUtils;
use n2n\persistence\orm\model\UnknownEntityPropertyException;
use n2n\reflection\property\InaccessiblePropertyException;
use n2n\reflection\property\InvalidPropertyAccessMethodException;
use n2n\reflection\property\UnknownPropertyException;
use n2n\util\ex\err\ConfigurationError;
use n2n\reflection\attribute\PropertyAttribute;
use n2n\util\type\TypeUtils;
use n2n\persistence\orm\property\EntityProperty;
use rocket\op\ei\component\prop\EiPropCollection;

class EiTypeSetup {
	private array $propertyNames;
	private array $sortedEiPropPaths = [];

	/**
	 * @param EiType $eiType
	 * @param EiPresetMode|null $eiPresetMode
	 * @param EiPresetProp[] $unassignedEiPresetPropsMap key must be property name.
	 */
	function __construct(private readonly EiType $eiType, private readonly ?EiPresetMode $eiPresetMode,
			private array $unassignedEiPresetPropsMap) {
		ArgUtils::valArray($unassignedEiPresetPropsMap, EiPresetProp::class);
		$this->propertyNames = array_keys($unassignedEiPresetPropsMap);
	}

	/**
	 * @return EntityModel
	 */
	function getEntityModel(): EntityModel {
		return $this->eiType->getEntityModel();
	}

	/**
	 * @return ReflectionClass
	 */
	function getClass(): ReflectionClass {
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
		$i = false;
		if ($propertyName !== null) {
			unset($this->unassignedEiPresetPropsMap[$propertyName]);
			$i = array_search($propertyName, $this->propertyNames);
		}

		$eiProp = $this->eiType->getEiMask()->getEiPropCollection()->add($id ?? $propertyName, $eiPropNature);
		if ($i !== false) {
			$this->sortedEiPropPaths[$i] = $eiProp->getEiPropPath();
		}
	}

	function addEiCmdNature(EiCmdNature $eiCmdNature, ?string $id = null) {
		$this->eiType->getEiMask()->getEiCmdCollection()->add($id, $eiCmdNature);
	}

	function addEiModNature(EiModNature $eiModNature, ?string $id = null) {
		$this->eiType->getEiMask()->getEiModCollection()->add($id, $eiModNature);
	}

	/**
	 * @param string $propertyName
	 * @param bool|null $editable
	 * @return PropertyAccessProxy
	 * @throws \ReflectionException
	 * @throws InaccessiblePropertyException
	 * @throws InvalidPropertyAccessMethodException
	 * @throws UnknownPropertyException
	 */
	function getPropertyAccessProxy(string $propertyName, bool $settingRequired) {
		$propertyAccessProxy = null;
		if (isset($this->unassignedEiPresetPropsMap[$propertyName])) {
			$propertyAccessProxy = $this->unassignedEiPresetPropsMap[$propertyName]->getPropertyAccessProxy();
		}

		if ($propertyAccessProxy !== null && ($settingRequired !== true || $propertyAccessProxy->isWritable())) {
			return $propertyAccessProxy;
		}

		$propertiesAnalyzer = new PropertiesAnalyzer($this->getClass());
		return $propertiesAnalyzer->analyzeProperty($propertyName, $settingRequired);
	}

	/**
	 * @param string $propertyName
	 * @param bool $required
	 * @return EntityProperty|null
	 */
	function getEntityProperty(string $propertyName, bool $required = false): ?EntityProperty {
		$entityProperty = null;
		if (isset($this->unassignedEiPresetPropsMap[$propertyName])) {
			$entityProperty = $this->unassignedEiPresetPropsMap[$propertyName]->getEntityProperty();
		}

		try {
			return $entityProperty ?? $this->getEntityModel()->getLevelEntityPropertyByName($propertyName);
		} catch (UnknownEntityPropertyException $e) {
			if (!$required) {
				return null;
			}

			throw $e;
		}
	}

	/**
	 * @param string $propertyName
	 * @return string
	 */
	function getPropertyLabel(string $propertyName) {
		$eiPresetProp = $this->unassignedEiPresetPropsMap[$propertyName] ?? null;

		return $eiPresetProp?->getLabel() ?? StringUtils::pretty($propertyName);
	}

	function createPropertyAttributeError(PropertyAttribute $propertyAttribute, \Throwable $previous = null,
			string $message = null): ConfigurationError {

		throw new ConfigurationError(
				$message ?? 'Could not initialize EiProp for '
						. TypeUtils::prettyReflPropName($propertyAttribute->getProperty()),
								$propertyAttribute->getFile(), $propertyAttribute->getLine(), previous: $previous);
	}

	function finalize(): void {
		ksort($this->sortedEiPropPaths);
		$this->eiType->getEiMask()->getEiPropCollection()->changeOrder($this->sortedEiPropPaths);
	}
}
