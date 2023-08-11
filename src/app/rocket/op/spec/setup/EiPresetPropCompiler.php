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

use rocket\attribute\EiPreset;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use n2n\reflection\property\AccessProxy;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeUtils;
use n2n\util\type\ArgUtils;
use n2n\reflection\attribute\Attribute;
use Throwable;
use n2n\util\ex\IllegalStateException;
use n2n\util\StringUtils;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\persistence\orm\model\EntityPropertyCollection;
use rocket\op\ei\EiPropPath;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ReflectionUtils;

class EiPresetPropCompiler {
	private PropertiesAnalyzer $propertiesAnalyzer;

	private array $eiPresetProps = [];

	function __construct(private readonly EnhancedEiPreset $enhancedEiPreset,
			private readonly EntityPropertyCollection $entityPropertyCollection,
			private readonly EiPropPath $parentEiPropPath) {
		$this->propertiesAnalyzer = new PropertiesAnalyzer($this->entityPropertyCollection->getClass());
	}

	/**
	 * @return EiPresetProp[]
	 */
	function compile(): array {
		$this->eiPresetProps = [];

		if ($this->enhancedEiPreset->getMode()?->isReadPropsMode()) {
			$this->analyzeAccessProxies($this->propertiesAnalyzer->analyzeProperties(true, false), false);
		} elseif ($this->enhancedEiPreset->getMode()?->isEditPropsMode()) {
			$this->analyzeAccessProxies($this->propertiesAnalyzer->analyzeProperties(true, false), true);
		}

		$this->analyzePropNotations($this->enhancedEiPreset->getReadPropNotations($this->parentEiPropPath), false);
		$this->analyzePropNotations($this->enhancedEiPreset->getEditPropNotations($this->parentEiPropPath), true);
		$this->analyzePropNotations($this->enhancedEiPreset->getParentPropNotations($this->parentEiPropPath), false);

		return $this->eiPresetProps;
	}

	/**
	 * @param PropNotation[] $propNotations
	 * @param bool $editable
	 * @return void
	 */
	private function analyzePropNotations(array $propNotations, bool $editable): void {
		foreach ($propNotations as $eiPropPathStr => $propNotation) {
			if (!$editable && isset($this->eiPresetProps[$eiPropPathStr])) {
				continue;
			}

			if ($editable && $this->enhancedEiPreset->containsReadProp($eiPropPathStr)) {
				throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPathStr,
						message: 'Already defined in readProps.');
			}

			if ($editable && isset($eiPresetProps[$eiPropPathStr])) {
				IllegalStateException::assertTrue($eiPresetProps[$eiPropPathStr]->isEditable());
				continue;
			}

			$this->ensureNotExcluded($eiPropPathStr);

			$propertyName = $propNotation->getEiPropPath()->getLastId();
			try {
				$eiPreset = $this->eiPresetProps[$eiPropPathStr] = $this->createEiPresetProp(
						$this->propertiesAnalyzer->analyzeProperty($propertyName, $editable), $editable, $propNotation->getLabel());
			} catch (\ReflectionException $e) {
				throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPathStr, $e);
			}

			if ($eiPreset->getEntityProperty()?->hasEmbeddedEntityPropertyCollection()) {
				$eiPresetPropCompiler = new EiPresetPropCompiler($this->enhancedEiPreset,
						$eiPreset->getEntityProperty()->getEmbeddedEntityPropertyCollection(),
						$propNotation->getEiPropPath());
				$this->eiPresetProps += $eiPresetPropCompiler->compile();
				continue;
			}

			if ($this->enhancedEiPreset->containsParentProp($eiPropPathStr)) {
				throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPathStr,
						message: 'Property has child properties but EntityProperty for "'
								. TypeUtils::prettyClassPropName($this->entityPropertyCollection->getClass(), $propertyName)
								. '" does not exist or does not support embedded EntityProperties.');
			}
		}
	}


	/**
	 * @param PropertyAccessProxy[] $accessProxies
	 * @param bool $editable
	 * @return void
	 */
	private function analyzeAccessProxies(array $accessProxies, bool $editable): void {
		foreach ($accessProxies as $accessProxy) {
			$propertyName = $accessProxy->getPropertyName();
			$eiPropPathStr = (string) $this->parentEiPropPath->ext($propertyName);

			IllegalStateException::assertTrue(!isset($this->eiPresetProps[$eiPropPathStr]));

			if ((!$accessProxy->isReadable() && !$accessProxy->isWritable())
					|| $this->enhancedEiPreset->containsExcludedPropNotation($eiPropPathStr)) {
				continue;
			}

			if (!$editable) {
				$this->eiPresetProps[$eiPropPathStr] = $this->createEiPresetProp($accessProxy,
						$this->enhancedEiPreset->containsEditProp($eiPropPathStr),
						$this->enhancedEiPreset->getLabel($eiPropPathStr));
				continue;
			}

			$this->eiPresetProps[$propertyName] = $this->createEiPresetProp($accessProxy,
					!$this->enhancedEiPreset->containsReadProp($eiPropPathStr)
							&& ($this->enhancedEiPreset->containsEditProp($eiPropPathStr) || $accessProxy->isWritable()),
					$this->enhancedEiPreset->getLabel($eiPropPathStr));
		}
	}

	private function ensureNotExcluded(string|EiPropPath $eiPropPath) {
		if ($this->enhancedEiPreset->containsExcludedPropNotation($eiPropPath)) {
			throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPath,
					message: 'Also defined in excludedProps.');
		}
	}

	/**
	 * @param PropertyAccessProxy $propertyAccessProxy
	 * @param bool $editable
	 * @return EiPresetProp
	 */
	private function createEiPresetProp(PropertyAccessProxy $propertyAccessProxy, bool $editable, ?string $label) {
		$propertyName = $propertyAccessProxy->getPropertyName();
		$entityProperty = null;

		if ($this->entityPropertyCollection->containsLevelEntityPropertyName($propertyName)) {
			$entityProperty = $this->entityPropertyCollection->getLevelEntityPropertyByName($propertyName);
		}

		return new EiPresetProp($propertyName, $propertyAccessProxy, $entityProperty, $editable,
				$label ?? StringUtils::pretty($propertyName));
	}

	/**
	 * @param array $unassignedEiPresetProps
	 * @return ConfigurationError
	 */
	function createUnassignedEiPresetPropsError(array $unassignedEiPresetProps): ConfigurationError {
		ArgUtils::valArray($unassignedEiPresetProps, EiPresetProp::class);

		return $this->enhancedEiPreset->createAttributeError( 'No suitable EiProps found for the following properties in '
				. $this->entityPropertyCollection->getClass()->getName() . ': '
				. implode(', ', array_map(fn (EiPresetProp $p) => $p->getName(), $unassignedEiPresetProps)));
	}






}
