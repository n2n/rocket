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

use n2n\reflection\property\PropertiesAnalyzer;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeUtils;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use n2n\util\StringUtils;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\persistence\orm\model\EntityPropertyCollection;
use rocket\op\ei\EiPropPath;
use n2n\reflection\property\UninitializedBehaviour;

class EiPresetPropCompiler {
	private PropertiesAnalyzer $propertiesAnalyzer;

	private EiPresetPropCollection $eiPresetPropCollection;

	function __construct(private readonly EnhancedEiPreset $enhancedEiPreset,
			private readonly EntityPropertyCollection $entityPropertyCollection,
			private readonly EiPropPath $parentEiPropPath) {
		$this->propertiesAnalyzer = new PropertiesAnalyzer($this->entityPropertyCollection->getClass(),
				uninitializedBehaviour: UninitializedBehaviour::RETURN_NULL);
	}

	/**
	 * @return EiPresetPropCollection
	 */
	function compile(): EiPresetPropCollection {
		$this->eiPresetPropCollection = new EiPresetPropCollection($this->parentEiPropPath, $this->entityPropertyCollection);

		if ($this->enhancedEiPreset->getMode()?->isReadPropsMode()) {
			$this->analyzeAccessProxies($this->propertiesAnalyzer->analyzeProperties(true, false), false);
		} elseif ($this->enhancedEiPreset->getMode()?->isEditPropsMode()) {
			$this->analyzeAccessProxies($this->propertiesAnalyzer->analyzeProperties(true, false), true);
		}

		$this->analyzePropNotations($this->enhancedEiPreset->getReadPropNotations($this->parentEiPropPath), false);
		$this->analyzePropNotations($this->enhancedEiPreset->getEditPropNotations($this->parentEiPropPath), true);
		$this->analyzePropNotations($this->enhancedEiPreset->getParentPropNotations($this->parentEiPropPath), false);

		return $this->eiPresetPropCollection;
	}

	/**
	 * @param PropNotation[] $propNotations
	 * @param bool $editable
	 * @return void
	 */
	private function analyzePropNotations(array $propNotations, bool $editable): void {
		foreach ($propNotations as $eiPropPathStr => $propNotation) {
			if (!$editable && $this->eiPresetPropCollection->containsEiPropPath($eiPropPathStr)) {
				continue;
			}

			if ($editable && $this->enhancedEiPreset->containsReadProp($eiPropPathStr)) {
				throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPathStr,
						message: 'Already defined in readProps.');
			}

			if ($editable && $this->eiPresetPropCollection->containsEiPropPath($eiPropPathStr)) {
				IllegalStateException::assertTrue($this->eiPresetPropCollection->getByEiPropPath($eiPropPathStr)->isEditable());
				continue;
			}

			$this->ensureNotExcluded($eiPropPathStr);

			$propertyName = $propNotation->getEiPropPath()->getLastId();
			try {
				$this->eiPresetPropCollection->add($eiPresetProp = $this->createEiPresetProp(
						EiPropPath::create($eiPropPathStr),
						$this->propertiesAnalyzer->analyzeProperty($propertyName, $editable), $editable,
						$propNotation->getLabel(), false));
			} catch (\ReflectionException $e) {
				throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPathStr, $e);
			}

			$this->checkForFork($eiPresetProp);
		}
	}

	private function checkForFork(EiPresetProp $eiPresetProp): void {
		if ($eiPresetProp->getEntityProperty()?->hasEmbeddedEntityPropertyCollection()) {
			$eiPresetPropCompiler = new EiPresetPropCompiler($this->enhancedEiPreset,
					$eiPresetProp->getEntityProperty()->getEmbeddedEntityPropertyCollection(),
					$eiPresetProp->getEiPropPath());
			$this->eiPresetPropCollection->addChildren($eiPresetPropCompiler->compile());
			return;
		}

		$eiPropPathStr = (string) $eiPresetProp->getEiPropPath();
		if ($this->enhancedEiPreset->containsParentProp($eiPropPathStr)) {
			throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPathStr,
					message: 'Property has child properties but EntityProperty for "'
					. TypeUtils::prettyClassPropName($this->entityPropertyCollection->getClass(),
							$eiPresetProp->getEiPropPath()->getLastId())
					. '" does not exist or does not support embedded EntityProperties.');
		}
	}


	/**
	 * @param PropertyAccessProxy[] $accessProxies
	 * @param bool $editable
	 * @return void
	 */
	private function analyzeAccessProxies(array $accessProxies, bool $editable): void {
		foreach ($accessProxies as $accessProxy) {
			$eiPropPath = $this->parentEiPropPath->ext($accessProxy->getPropertyName());
			$eiPropPathStr = (string) $eiPropPath;

			IllegalStateException::assertTrue(!$this->eiPresetPropCollection->containsEiPropPath($eiPropPathStr));

			if ((!$accessProxy->isReadable() && !$accessProxy->isWritable())
					|| $this->enhancedEiPreset->containsExcludedPropNotation($eiPropPathStr)) {
				continue;
			}

			if (!$editable) {
				$eiPresetProp = $this->createEiPresetProp($eiPropPath, $accessProxy,
						$this->enhancedEiPreset->containsEditProp($eiPropPathStr),
						$this->enhancedEiPreset->getLabel($eiPropPathStr), true);
			} else {
				$eiPresetProp = $this->createEiPresetProp($eiPropPath, $accessProxy,
						!$this->enhancedEiPreset->containsReadProp($eiPropPathStr)
						&& ($this->enhancedEiPreset->containsEditProp($eiPropPathStr) || $accessProxy->isWritable()),
						$this->enhancedEiPreset->getLabel($eiPropPathStr), true);
			}

			$this->eiPresetPropCollection->add($eiPresetProp);

			$this->checkForFork($eiPresetProp);
		}
	}

	private function ensureNotExcluded(string|EiPropPath $eiPropPath): void {
		if ($this->enhancedEiPreset->containsExcludedPropNotation($eiPropPath)) {
			throw $this->enhancedEiPreset->createEiPresetAttributeError($eiPropPath,
					message: 'Also defined in excludedProps.');
		}
	}

	private function createEiPresetProp(EiPropPath $eiPropPath, PropertyAccessProxy $propertyAccessProxy, bool $editable,
			?string $label, bool $autoDetected): EiPresetProp {
		$propertyName = $propertyAccessProxy->getPropertyName();
		$entityProperty = null;

		if ($this->entityPropertyCollection->containsLevelEntityPropertyName($propertyName)) {
			$entityProperty = $this->entityPropertyCollection->getLevelEntityPropertyByName($propertyName);
		}

		return new EiPresetProp($eiPropPath, $propertyAccessProxy, $entityProperty, $editable,
				$label ?? StringUtils::pretty($propertyName), $autoDetected);
	}

	/**
	 * @param array $unassignedEiPresetProps
	 * @return ConfigurationError
	 */
	function createUnassignedEiPresetPropsError(array $unassignedEiPresetProps): ConfigurationError {
		ArgUtils::valArray($unassignedEiPresetProps, EiPresetProp::class);

		return $this->enhancedEiPreset->createAttributeError( 'No suitable EiProps found for the following properties in '
				. $this->entityPropertyCollection->getClass()->getName() . ': '
				. implode(', ', array_map(fn (EiPresetProp $p) => $p->getEiPropPath(), $unassignedEiPresetProps)));
	}






}
