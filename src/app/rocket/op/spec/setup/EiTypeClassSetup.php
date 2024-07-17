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

use n2n\reflection\attribute\AttributeSet;
use n2n\reflection\ReflectionContext;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\component\prop\EiPropNature;
use n2n\util\type\ArgUtils;
use rocket\op\ei\EiType;
use n2n\persistence\orm\model\EntityPropertyCollection;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\reflection\property\InaccessiblePropertyException;
use n2n\reflection\property\InvalidPropertyAccessMethodException;
use n2n\reflection\property\UnknownPropertyException;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\model\UnknownEntityPropertyException;
use n2n\util\StringUtils;
use n2n\persistence\orm\property\IdDef;

class EiTypeClassSetup {

	/**
	 * @var EiPresetProp[]
	 */
	private array $unassignedEiPresetPropsMap;

	function __construct(private readonly EiType $eiType,
			private readonly ?EiPresetPropCollection $eiPresetPropCollection,
			private readonly EntityPropertyCollection $entityPropertyCollection) {
		$this->unassignedEiPresetPropsMap = $this->eiPresetPropCollection?->getAll() ?? [];
	}

	function getEntityPropertyCollection(): EntityPropertyCollection {
		return $this->entityPropertyCollection;
	}

	function getClass(): \ReflectionClass {
		return $this->getEntityPropertyCollection()->getClass();
	}

	function getIdDef(): IdDef {
		return $this->eiType->getEntityModel()->getIdDef();
	}

	/**
	 * @return AttributeSet
	 */
	function getAttributeSet(): AttributeSet {
		return ReflectionContext::getAttributeSet($this->getClass());
	}

	function createEiPropPath(string $propertyName): EiPropPath {
		return $this->eiPresetPropCollection?->getParentEiPropPath()->ext($propertyName)
				?? new EiPropPath([$propertyName]);
	}

	/**
	 * @return EiPresetProp[]
	 */
	function getUnassignedEiPresetProps(): array {
		return $this->unassignedEiPresetPropsMap;
	}

	/**
	 * @param string $propertyName
	 * @param bool $settingRequired
	 * @return PropertyAccessProxy
	 * @throws \ReflectionException
	 * @throws InaccessiblePropertyException
	 * @throws InvalidPropertyAccessMethodException
	 * @throws UnknownPropertyException
	 */
	function getPropertyAccessProxy(string $propertyName, bool $settingRequired): PropertyAccessProxy {
		$eiPropPathStr = (string) $this->createEiPropPath($propertyName);

		$propertyAccessProxy = null;
		if (isset($this->unassignedEiPresetPropsMap[$eiPropPathStr])) {
			$propertyAccessProxy = $this->unassignedEiPresetPropsMap[$eiPropPathStr]->getPropertyAccessProxy();
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
		$eiPropPathStr = (string) $this->createEiPropPath($propertyName);

		$entityProperty = null;
		if (isset($this->unassignedEiPresetPropsMap[$eiPropPathStr])) {
			$entityProperty = $this->unassignedEiPresetPropsMap[$eiPropPathStr]->getEntityProperty();
		}

		try {
			return $entityProperty ?? $this->getEntityPropertyCollection()->getLevelEntityPropertyByName($propertyName);
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
	function getPropertyLabel(string $propertyName): string {
		$eiPropPathStr = (string) $this->createEiPropPath($propertyName);
		$eiPresetProp = $this->unassignedEiPresetPropsMap[$eiPropPathStr] ?? null;

		return $eiPresetProp?->getLabel() ?? StringUtils::pretty($propertyName);
	}

	function addEiPropNature(EiPropPath $eiPropPath, EiPropNature $eiPropNature): void {
		$parentEiPropPath = $this->eiPresetPropCollection?->getParentEiPropPath();
		if ($parentEiPropPath !== null && !$eiPropPath->isChildOf($parentEiPropPath)) {
			throw new \InvalidArgumentException('EiPropPath must be a direct child of  '
					. ($parentEiPropPath->isEmpty() ? '<root>' : $parentEiPropPath) . '. Given: "' . $eiPropPath . '"');
		}

		$this->eiType->getEiMask()->getEiPropCollection()->put($eiPropPath, $eiPropNature);

		unset($this->unassignedEiPresetPropsMap[(string) $eiPropPath]);
	}
}