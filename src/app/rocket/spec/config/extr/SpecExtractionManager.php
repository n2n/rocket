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

use n2n\util\config\InvalidConfigurationException;
use rocket\spec\config\UnknownSpecException;
use rocket\spec\config\source\ModularConfigSource;
use n2n\util\ex\IllegalStateException;
use rocket\spec\config\InvalidEiMaskConfigurationException;
use rocket\core\model\MenuItem;
use rocket\core\model\UnknownMenuItemException;
use n2n\reflection\ArgUtils;

class SpecExtractionManager {
	private $modularConfigSource;
	private $moduleNamespaces;
	private $specCsDecs = array();
	private $specExtractions = array();
	private $eiSpecExtractions = array();
	private $unboundCommonEiMaskExtractionGroups = array();
	private $menuItemExtractions = array();
	
	public function __construct(ModularConfigSource $moduleConfigSource, array $moduleNamespaces) {
		$this->modularConfigSource = $moduleConfigSource;
		$this->moduleNamespaces = $moduleNamespaces;
	}
	
	public function initialize() {
		foreach ($this->moduleNamespaces as $moduleNamespace) {
			$moduleNamespace = (string) $moduleNamespace;
			
			if (!$this->modularConfigSource->containsModuleNamespace($moduleNamespace)) {
				$this->specCsDecs[$moduleNamespace] = null;
				continue;
			}
			
			$this->specCsDecs[$moduleNamespace] = $specCsDec = new SpecConfigSourceDecorator(
					$this->modularConfigSource->getConfigSourceByModuleNamespace($moduleNamespace), $moduleNamespace);
			
			$specCsDec->load();
		}
		
		$this->initSpecExtractions();
		$this->initCommonEiMaskExtractions();
		$this->initMenuItemExtractions();
	}
	
	private function initSpecExtractions() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
				
			foreach ($specCsDec->getSpecExtractions() as $specId => $spec) {
				if (isset($this->specExtractions[$specId])) {
					throw $this->createDuplicatedSpecIdException($specId);
				}
					
				$this->specExtractions[$specId] = $spec;
					
				if ($spec instanceof EiSpecExtraction) {
					$entityClassName = $spec->getEntityClassName();
					if (isset($this->eiSpecExtractions[$entityClassName])) {
						throw $this->createDuplicatedEntityClassNameException($entityClassName);
					}
						
					$this->eiSpecExtractions[$spec->getEntityClassName()] = $spec;
				}
			}
		}
	}
	
	private function initCommonEiMaskExtractions() {
		$this->unboundCommonEiMaskExtractionGroups = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getCommonEiMaskEiSpecIds() as $eiSpecId) {
				if (!isset($this->unboundCommonEiMaskExtractionGroups[$eiSpecId])) {
					$this->unboundCommonEiMaskExtractionGroups[$eiSpecId] = array();
				}
				
				foreach ($specCsDec->getCommonEiMaskExtractionsByEiSpecId($eiSpecId) as $eiMaskId => $eiMaskExtraction) {
					if (isset($this->unboundCommonEiMaskExtractionGroups[$eiSpecId][$eiMaskId])) {
						throw new $this->createDuplicatedEiMaskIdException($eiSpecId, $eiMaskId);
					}
					
					if (isset($this->specExtractions[$eiSpecId]) && !($this->specExtractions[$eiSpecId] instanceof EiSpecExtraction)) {
						throw new InvalidConfigurationException('Invalid configuration in: ' . $specCsDec->getDataSource(), 0, 
								new InvalidEiMaskConfigurationException('EiMask with id \'' . $eiMaskId 
										. '\' was configured not for CustomSpec \'' . $eiSpecId . '\.'));
					}
						
					$this->unboundCommonEiMaskExtractionGroups[$eiSpecId][$eiMaskId] = $eiMaskExtraction;
				}
			}
		}
		
		foreach ($this->unboundCommonEiMaskExtractionGroups as $eiSpecId => $commonEiMaskExtractions) {
			if (!isset($this->specExtractions[$eiSpecId])) continue;
			
			$this->specExtractions[$eiSpecId]->setCommonEiMaskExtractions($commonEiMaskExtractions);
			unset($this->unboundCommonEiMaskExtractionGroups[$eiSpecId]);
		}
	}
	
	private function initMenuItemExtractions() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
	
			foreach ($specCsDec->getMenuItemExtractions() as $menuItemId => $menuItemExtraction) {
				if (isset($this->menuItemExtractions[$menuItemId])) {
					throw $this->createDuplicatedMenuItemIdException($menuItemId);
				}
					
				$this->menuItemExtractions[$menuItemId] = $menuItemExtraction;
			}
		}
	}
		
	private function createDuplicatedSpecIdException($specId) {
		$configSources = array();
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			if ($specCsDec->containsSpecId($specId)) {
				$configSources[] = $specCsDec->getConfigSource();
			}
		}
		
		throw new InvalidConfigurationException('Spec with id \'' . $specId 
				. '\' is defined in multiple data sources: ' . implode(', ', $configSources));
	}
	
	private function createDuplicatedEntityClassNameException($entityClassName) {
		$configSources = array();
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			if ($specCsDec->containsEntityClassName($entityClassName)) {
				$configSources[] = $specCsDec->getConfigSource();
			}
		}
		
		return new InvalidConfigurationException('EiSpec for entity class \'' . $entityClassName 
				. '\' is defined in multiple times in: ' . implode(', ', $configSources));
	}
	
	private function createDuplicatedEiMaskIdException(string $eiSpecId, string $eiMaskId): InvalidConfigurationException {
		$dataSources = array();
		foreach ($this->specCsDecs as $specConfig) {
			if ($specConfig === null) continue;
			
			if ($specConfig->containsEiMaskId($eiSpecId, $eiMaskId)) {
				$dataSources[] = $specConfig->getDataSource();
			}
		}
		
		return new InvalidConfigurationException('EiMask with id \'' . $eiMaskId 
				. '\' for EiSpec \'' . $eiSpecId . '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
	}
	
	private function createDuplicatedMenuItemIdException($menuItemId): InvalidConfigurationException {
		$dataSources = array();
		foreach ($this->specCsDecs as $specConfig) {
			if ($specConfig->containsMenuItemId($menuItemId)) {
				$dataSources[] = $specConfig->getDataSource();
			}
		}
	
		throw new InvalidConfigurationException('MenuItem with id \'' . $menuItemId
				. '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
	}
	
	public function getSpecIds(): array {
		return array_keys($this->specExtractions);
	}
	
	public function containsSpecId($id) {
		return isset($this->specExtractions[$id]);
	}
	
	/**
	 * @return SpecExtraction[] 
	 */
	public function getSpecExtractions(): array {
		return $this->specExtractions;
	}
	
	/**
	 * @param string $id
	 * @throws UnknownSpecException
	 * @return SpecExtraction
	 */
	public function getSpecExtractionById($id): SpecExtraction {
		if (isset($this->specExtractions[$id])) {
			return $this->specExtractions[$id];
		}
		
		throw new UnknownSpecException('No Spec with id \'' . $id . '\' defined in: ' 
				. $this->buildConfigSourceString());
	}
	
	public function containsEiSpecEntityClassName($className): bool {
		return isset($this->eiSpecExtractions[$className]);
	}
	
	public function getEiSpecExtractionByClassName($className): EiSpecExtraction {
		if (isset($this->eiSpecExtractions[$className])) {
			return $this->eiSpecExtractions[$className];
		}
		
		throw new UnknownSpecException('No EiSpec for Entity \'' . $className . '\' defined in: ' 
				. $this->buildConfigSourceString());
	}
	
	/**
	 * @return EiSpecExtraction[]
	 */
	public function getEiSpecExtractions(): array {
		return $this->eiSpecExtractions;
	}
	
	public function getUnboundCommonEiMaskExtractionGroups(): array {
		return $this->unboundCommonEiMaskExtractionGroups;
	}
	
	private function buildConfigSourceString(): string {
		$configSourceStrs = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$configSourceStrs[] = (string) $specCsDec->getConfigSource();
		}
		
		return 'config source bundle (' . implode(', ', $configSourceStrs) . ')';
	}
	
	public function addSpec(SpecExtraction $specExtraction) {
		$id = $specExtraction->getId();
		if (isset($this->specExtractions[$id])) {
			throw new IllegalStateException('Duplicated spec id: ' . $id);
		}
		
		$this->specExtractions[$id] = $specExtraction;
		
		if ($specExtraction instanceof EiSpecExtraction) {
			$entityClassName = $specExtraction->getEntityClassName();
			
			if (isset($this->eiSpecExtractions[$entityClassName])) {
				throw new IllegalStateException('EiSpec for Entity already defined: ' . $entityClassName);
			}
			
			$this->eiSpecExtractions[$entityClassName] = $specExtraction;
		} else {
			ArgUtils::assertTrue($specExtraction instanceof CustomSpecExtraction);
		}
		
		unset($this->unboundCommonEiMaskExtractionGroups[$specExtraction->getId()]);
	}
	
	public function removeSpecById(string $specId) {
		if (isset($this->specExtractions[$specId]) && $this->specExtractions[$specId] instanceof EiSpecExtraction) {
			unset($this->eiSpecExtractions[$this->specExtractions[$specId]->getEntityClassName()]);
		}
		
		unset($this->specExtractions[$specId]);
		unset($this->unboundCommonEiMaskExtractionGroups[$specId]);
	}
	

	public function getMenuItemExtractionById(string $id): MenuItemExtraction {
		if (isset($this->menuItemExtractions[$id])) {
			return $this->menuItemExtractions[$id];
		}
	
		throw new UnknownMenuItemException('No MenuItem with id \'' . $id . '\' defined in: '
				. $this->buildConfigSourceString());
	}

	public function addMenuItem(MenuItemExtraction $menuItemExtraction) {
		$this->menuItemExtractions[$menuItemExtraction->getId()] = $menuItemExtraction;
	}
	
	public function removeMenuItemById(string $menuItemId) {
		unset($this->menuItemExtractions[$menuItemId]);
	}
	
	/**
	 * @param string $moduleNamespace
	 * @throws IllegalStateException
	 * @return \rocket\spec\config\extr\SpecConfigSourceDecorator
	 */
	private function getSpecCsDescByModuleNamespace(string $moduleNamespace): SpecConfigSourceDecorator {
		if (isset($this->specCsDecs[$moduleNamespace])) {
			return $this->specCsDecs[$moduleNamespace];
		}
	
		if (array_key_exists($moduleNamespace, $this->specCsDecs)) {
			return $this->specCsDecs[$moduleNamespace] = new SpecConfigSourceDecorator(
					$this->modularConfigSource->getConfigSourceByModuleNamespace($moduleNamespace), $moduleNamespace);
		}
	
		throw new IllegalStateException('Unknown module namespace: ' . $moduleNamespace);
	}
	
	public function flush() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$specCsDec->clear();
		}
		
		foreach ($this->specExtractions as $specId => $specExtraction) {
			$this->getSpecCsDescByModuleNamespace($specExtraction->getModuleNamespace())
					->addSpecExtraction($specExtraction);
						
			if ($specExtraction instanceof EiSpecExtraction) {
				foreach ($specExtraction->getCommonEiMaskExtractions() as $commonEiMaskExtraction) {
					$this->getSpecCsDescByModuleNamespace($commonEiMaskExtraction->getModuleNamespace())
							->addCommonEiMaskExtraction($specId, $commonEiMaskExtraction);
				}
			}
		}
		
		foreach ($this->unboundCommonEiMaskExtractionGroups as $eiSpecId => $commonEiMaskExtractions) {
			foreach ($commonEiMaskExtractions as $commonEiMaskExtraction) {
				$this->getSpecCsDescByModuleNamespace($commonEiMaskExtraction->getModuleNamespace())
						->addCommonEiMaskExtraction($specId, $commonEiMaskExtraction);
			}
		}
		
		foreach ($this->menuItemExtractions as $menuItemId => $menuItemExtraction) {
			$this->getSpecCsDescByModuleNamespace($menuItemExtraction->getModuleNamespace())
					->addMenuItemExtraction($menuItemExtraction);
		}
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$specCsDec->flush();
		}
	}
}
