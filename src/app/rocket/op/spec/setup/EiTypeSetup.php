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
use rocket\op\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\model\EntityPropertyCollection;

class EiTypeSetup {
	private array $eiTypeClassSetups;
	private ?array $sortedEiPropPaths = null;

	function __construct(private readonly EiType $eiType, private readonly ?EiPresetMode $eiPresetMode,
			private readonly ?EiPresetPropCollection $rootEiPresetPropCollection) {
		IllegalStateException::assertTrue($this->rootEiPresetPropCollection === null
				|| $this->rootEiPresetPropCollection->getParentEiPropPath()->isEmpty());

		if ($this->rootEiPresetPropCollection === null) {
			$this->eiTypeClassSetups = $this->createAutoEiTypeClassSetups($this->getEntityModel());
		} else {
			$this->sortedEiPropPaths = $this->rootEiPresetPropCollection->getEiPropPaths(true);
			$this->eiTypeClassSetups = array_map(
					fn($c) => new EiTypeClassSetup($this->eiType, $c, $c->getEntityPropertyCollection()),
					$this->rootEiPresetPropCollection->toArray());
		}
	}

	private function createAutoEiTypeClassSetups(EntityPropertyCollection $entityPropertyCollection): array {
		$setups = [new EiTypeClassSetup($this->eiType, null, $entityPropertyCollection) ];
		foreach ($entityPropertyCollection->getEntityProperties() as $entityProperty) {
			if (!$entityProperty->hasEmbeddedEntityPropertyCollection()) {
				continue;
			}

			array_push($setups, ...$this->createAutoEiTypeClassSetups(
					$entityProperty->getEmbeddedEntityPropertyCollection()));
		}
		return $setups;
	}

	/**
	 * @return AttributeSet
	 */
	function getAttributeSet(): AttributeSet {
		return ReflectionContext::getAttributeSet($this->getEntityModel()->getClass());
	}

	/**
	 * @return EntityModel
	 */
	function getEntityModel(): EntityModel {
		return $this->eiType->getEntityModel();
	}

	function getClass(): ReflectionClass {
		return $this->getEntityModel()->getClass();
	}

	/**
	 * @return EiTypeClassSetup[]
	 */
	function getEiTypeClassSetups(): array {
		return $this->eiTypeClassSetups;
	}

	/**
	 * @return EiPresetMode|null
	 */
	function getEiPresetMode(): ?EiPresetMode {
		return $this->eiPresetMode;
	}


	/**
	 * @return EiPresetProp[]
	 */
	function getUnassignedEiPresetProps(): array {
		$eiPresetProps = [];
		foreach ($this->eiTypeClassSetups as $eiTypeClassSetup) {
			foreach ($eiTypeClassSetup->getUnassignedEiPresetProps() as $eiPresetProp) {
				$eiPresetProps[] = $eiPresetProp;
			}
		}
		return $eiPresetProps;
	}

	function addEiCmdNature(EiCmdNature $eiCmdNature, ?string $id = null) {
		$this->eiType->getEiMask()->getEiCmdCollection()->add($id, $eiCmdNature);
	}

	function addEiModNature(EiModNature $eiModNature, ?string $id = null) {
		$this->eiType->getEiMask()->getEiModCollection()->add($id, $eiModNature);
	}



	function createPropertyAttributeError(PropertyAttribute $propertyAttribute, \Throwable $previous = null,
			string $message = null): ConfigurationError {

		throw new ConfigurationError(
				$message ?? 'Could not initialize EiProp for '
						. TypeUtils::prettyReflPropName($propertyAttribute->getProperty()),
								$propertyAttribute->getFile(), $propertyAttribute->getLine(), previous: $previous);
	}

	function finalize(): void {
//		ksort($this->sortedEiPropPaths);

		if ($this->sortedEiPropPaths !== null) {
			$this->eiType->getEiMask()->getEiPropCollection()->changeOrder($this->sortedEiPropPaths);
		}
	}
}
