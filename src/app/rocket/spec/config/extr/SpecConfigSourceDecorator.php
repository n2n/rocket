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
namespace rocket\spec\config\extr;

use n2n\util\config\source\WritableConfigSource;
use n2n\util\config\Attributes;
use n2n\util\config\InvalidConfigurationException;
use n2n\util\config\AttributesException;
use rocket\spec\config\InvalidSpecConfigurationException;
use rocket\spec\config\InvalidEiMaskConfigurationException;
use n2n\reflection\ArgUtils;

/**
 * Decorates the ConfigSource of a spec configuration from a single module and provides simplified interface to read
 * from and write to this ConfigSource. This class is used by {@see SpecExtractionManager}.
 */
class SpecConfigSourceDecorator {
	private $specRawer;
	private $configSource;
	private $moduleNamespace;
	
	private $attributes;
	private $specExtractions = array();
	private $eiMaskExtensionExtractionGroups = array();
	private $eiModificatorExtractionGroups = array();
	private $menuItemExtractions = array();
	
	/**
	 * @param WritableConfigSource $configSource
	 * @param string $moduleNamespace
	 */
	public function __construct(WritableConfigSource $configSource, string $moduleNamespace) {
		$this->attributes = new Attributes();
		$this->configSource = $configSource;
		$this->moduleNamespace = $moduleNamespace;
	} 
	
	/**
	 * @return string
	 */
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	/**
	 * @return \n2n\util\config\source\WritableConfigSource
	 */
	public function getConfigSource() {
		return $this->configSource;
	}
	
	/**
	 * Reads the decorated ConfigSource and uses {@see SpecExtractor} to extract all
	 * {@see SpecExtraction}s, {@see EiMaskExtensionExtraction}s, {@see MenuItemExtraction}s
	 * and overwrites the matching properties on this class. You can access these properties 
	 * through the getter methods.  
	 * @throws InvalidConfigurationException
	 */
	public function extract() {
		$this->attributes = new Attributes($this->configSource->readArray());
		
		$specExtractor = new SpecExtractor($this->attributes, $this->moduleNamespace);
		
		try {
			$this->specExtractions = $specExtractor->extractSpecs();
			$this->eiMaskExtensionExtractionGroups = $specExtractor->extractEiMaskGroups();
			$this->eiModificatorExtractionGroups = $specExtractor->extractEiModificatorGroups();
			$this->menuItemExtractions = $specExtractor->extractMenuItems();
		} catch (AttributesException $e) {
			throw $this->createDataSourceException($e);
		} catch (InvalidSpecConfigurationException $e) {
			throw $this->createDataSourceException($e);
		} catch (InvalidEiMaskConfigurationException $e) {
			throw $this->createDataSourceException($e);
		}
	}
	
	/**
	 * Uses {@see SpecRawer} to do the opposite of {@see self::extract()}.
	 */
	public function flush() {
		$this->specRawer = new SpecRawer($this->attributes);
		$this->specRawer->rawSpecs($this->specExtractions);
		$this->specRawer->rawEiMasks($this->eiMaskExtensionExtractionGroups);
		$this->specRawer->rawEiModificatorExtractionGroups($this->eiModificatorExtractionGroups);
		$this->specRawer->rawMenuItems($this->menuItemExtractions);
		
		$this->configSource->writeArray($this->attributes->toArray());
	}
	
	/**
	 * 
	 */
	public function clear() {
		if ($this->specRawer !== null) {
			$this->specRawer->clear();
		}
		
		$this->attributes = new Attributes();
		
		$this->specExtractions = array();
		$this->eiMaskExtensionExtractionGroups = array();
		$this->eiModificatorExtractionGroups = array();
		$this->menuItemExtractions = array();
	}
		
	/**
	 * @return \rocket\spec\config\extr\SpecExtraction[]
	 */
	public function getSpecExtractions() {		
		return $this->specExtractions;	
	}
	
	/**
	 * @param SpecExtraction[] $specExtractions
	 */
	public function setSpecExtractions(array $specExtractions) {
		ArgUtils::valArray($specExtractions, SpecExtraction::class);
		$this->specExtractions = $specExtractions;
	}
	
	/**
	 * @param SpecExtraction $specExtraction
	 */
	public function addSpecExtraction(SpecExtraction $specExtraction) {
		$this->specExtractions[$specExtraction->getId()] = $specExtraction;
	}
	
	/**
	 * @param \Exception $previous
	 * @throws InvalidConfigurationException
	 */
	private function createDataSourceException(\Exception $previous) {
		throw new InvalidConfigurationException('Configruation error in data source: ' . $this->configSource, 0, $previous);
	}
	
	public function getEiMaskEiTypeIds() {
		return array_keys($this->eiMaskExtensionExtractionGroups);
	}
	
	public function getEiMaskExtensionExtractionsByEiTypeId($eiTypeId) {
		if (isset($this->eiMaskExtensionExtractionGroups[$eiTypeId])) {
			return $this->eiMaskExtensionExtractionGroups[$eiTypeId];
		}

		return array();
	}
	
	public function setEiMaskExtensionExtractions($eiTypeId, array $eiMaskExtensionExtractions) {
		$this->eiMaskExtensionExtractionGroups[$eiTypeId] = $eiMaskExtensionExtractions;
	}
	
	public function addEiMaskExtensionExtraction($eiTypeId, EiMaskExtensionExtraction $eiMaskExtensionExtraction) {
		if (!isset($this->eiMaskExtensionExtractionGroups[$eiTypeId])) {
			$this->eiMaskExtensionExtractionGroups[$eiTypeId] = array();
		}
		
		$this->eiMaskExtensionExtractionGroups[$eiTypeId][] = $eiMaskExtensionExtraction;
	}
	
	public function getEiModificatorsEiTypeIds() {
		return array_keys($this->eiModificatorExtractionGroups);
	}
	
	public function getEiModificatorExtractionsByEiTypeId(string $eiTypeId) {
		if (isset($this->eiModificatorExtractionGroups[$eiTypeId])) {
			return $this->eiModificatorExtractionGroups[$eiTypeId];
		}

		return array();
	}
	
	public function setEiModificatorExtractions(string $eiTypeId, array $eiModificatorExtractions) {
		ArgUtils::valArray($eiModificatorExtractions, EiModificatorExtraction::class);
		$this->eiModificatorExtractionGroups[$eiTypeId] = $eiModificatorExtractions;
	}
	
	public function addEiModificatorExtraction(string $eiTypeId, EiModificatorExtraction $eiModificatorExtraction) {
		if (!isset($this->eiModificatorExtractionGroups[$eiTypeId])) {
			$this->eiModificatorExtractionGroups[$eiTypeId] = array();
		}
		
		$this->eiModificatorExtractionGroups[$eiTypeId][] = $eiModificatorExtraction;
	}
	
	public function containsSpecId($specId) {
		return isset($this->specExtractions[$specId]);
	}
	
	public function containsEntityClassName(string $entityClassName): bool {
		foreach ($this->specExtractions as $id => $spec) {
			if ($spec instanceof EiTypeExtraction && $spec->getEntityClassName() == $entityClassName) {
				return true;
			}
		}
		
		return false;
	}
	
	public function containsEiMaskId(string $eiTypeId, string $eiMaskId): bool {
		return isset($this->eiMaskExtensionExtractionGroups[$eiTypeId][$eiMaskId]);
	}
	
	public function containsEiModificatorId(string $eiTypeId, string $eiModificatorId): bool {
		return isset($this->eiModificatorExtractionGroups[$eiTypeId][$eiModificatorId]);
	}
	
	public function containsMenuItemId(string $menuItemId): bool {
		return isset($this->menuItemExtractions[$menuItemId]);
	}
	
	public function getMenuItemExtractions(): array {
		return $this->menuItemExtractions;
	}
	
	public function setMenuItemExtractions(array $menuItemExtractions) {
		ArgUtils::valArray($menuItemExtractions, MenuItemExtraction::class);
		$this->menuItemExtractions = $menuItemExtractions;
	}
	
	public function addMenuItemExtraction(MenuItemExtraction $menuItemExtraction) {
		$this->menuItemExtractions[$menuItemExtraction->getId()] = $menuItemExtraction;
	}
}
