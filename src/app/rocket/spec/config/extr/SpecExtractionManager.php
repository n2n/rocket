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
use rocket\core\model\UnknownMenuItemException;
use n2n\reflection\ArgUtils;
use rocket\spec\config\TypePath;

/**
 * <p>This manager allows you to read und write spec configurations usually located in 
 * 	<code>[n2n-root]/var/etc/[module]/rocket/specs.json</code>. It is used by 
 * 	the {@see \rocket\spec\config\SpecManager} to load the current configuration.</p>
 * 
 * <p>It is also used by the dev tool Hangar {@link https://dev.n2n.rocks/en/hangar/docs} 
 * 	to manipulate spec configurations.</p>
 */
class SpecExtractionManager {
	private $init = false;
	private $modularConfigSource;
	private $moduleNamespaces;
	
	/**
	 * @var SpecConfigSourceDecorator[]
	 */
	private $specCsDecs = array();
	
	private $customTypeExtractions = array();
	private $eiTypeExtractions = array();
	private $eiTypeExtractionCis = array();
	private $eiTypeExtensionExtractionGroups = array();
	private $eiModificatorExtractions = array();
	private $menuItemExtractions = array();
	
	/**
	 * @param ModularConfigSource $moduleConfigSource
	 * @param string[] $moduleNamespaces Namespaces of all modules which spec configurations shall be loaded. 
	 */
	public function __construct(ModularConfigSource $moduleConfigSource, array $moduleNamespaces) {
		$this->modularConfigSource = $moduleConfigSource;
		$this->moduleNamespaces = $moduleNamespaces;
	}
	
	/**
	 * @return \rocket\spec\config\source\ModularConfigSource
	 */
	public function getModularConfigSource() {
		return $this->modularConfigSource;
	}
	
	/**
	 * Searches all available module configurations. Nothing will be extracted but {@see self::hashCode()} gives the 
	 * right result.
	 */
	public function load() {
		$this->specCsDecs = array();
		
		foreach ($this->moduleNamespaces as $moduleNamespace) {
			$moduleNamespace = (string) $moduleNamespace;
				
			if (!$this->modularConfigSource->containsModuleNamespace($moduleNamespace)) {
				$this->specCsDecs[$moduleNamespace] = null;
				continue;
			}
				
			$this->specCsDecs[$moduleNamespace] = new SpecConfigSourceDecorator(
					$this->modularConfigSource->getOrCreateConfigSourceByModuleNamespace($moduleNamespace), $moduleNamespace);
		}
	}
	
	/**
	 * 
	 */
	public function initialize() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			$specCsDec->extract();
		}
		
		$this->dingselTypes();
		$this->dingselEiTypeExtensions();
		$this->dingselEiModificatorExtractions();
		$this->dingselMenuItemExtractions();
		
		$this->init = true;
	}
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->init;
	}
	
	private function dingselTypes() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
				
			foreach ($specCsDec->getCustomTypeExtractions() as $typeId => $customType) {
				if (isset($this->customTypeExtractions[$typeId]) || isset($this->eiTypeExtractions[$typeId])) {
					throw $this->createDuplicatedSpecIdException($typeId);
				}
				
				$this->customTypeExtractions[$typeId] = $customType;
			}
			
			foreach ($specCsDec->getEiTypeExtractions() as $typeId => $eiType) {
				if (isset($this->customTypeExtractions[$typeId]) || isset($this->eiTypeExtractions[$typeId])) {
					throw $this->createDuplicatedSpecIdException($typeId);
				}
				$this->eiTypeExtractions[$typeId] = $eiType;
				
				$entityClassName = $eiType->getEntityClassName();
				if (isset($this->eiTypeExtractionCis[$entityClassName])) {
					throw $this->createDuplicatedEntityClassNameException($entityClassName);
				}
				$this->eiTypeExtractionCis[$entityClassName] = $eiType;
			}
		}
	}
	
	private function dingselEiTypeExtensions() {
		$this->eiTypeExtensionExtractionGroups = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getEiTypeExtensionExtractionGroups() 
					as $extendedTypePathStr => $eiTypeExtensionExtractions) {
				if (!isset($this->eiTypeExtensionExtractionGroups[$extendedTypePathStr])) {
					$this->eiTypeExtensionExtractionGroups[$extendedTypePathStr] = array();
				}
				
				foreach ($eiTypeExtensionExtractions as $eiTypeExtensionExtraction) {
					$id = $eiTypeExtensionExtraction->getId();
					
					if (isset($this->eiTypeExtensionExtractionGroups[$extendedTypePathStr][$id])) {
						throw new $this->createDuplicatedEiMaskIdException($extendedTypePath, $id);
					}
						
					if (isset($this->customTypeExtractions[$eiTypeExtensionExtraction->getExtendedTypePath()->getEiTypeId()])) {
						throw new InvalidConfigurationException('Invalid configuration in: ' . $specCsDec->getDataSource(), 0, 
								new InvalidEiMaskConfigurationException('EiMask with id \'' . $eiMaskId 
										. '\' was configured not for CustomType \'' . $eiTypeId . '\.'));
					}
						
					$this->eiTypeExtensionExtractionGroups[$extendedTypePathStr][$id] = $eiTypeExtensionExtraction;
				}
			}
		}
	}
	
	private function dingselEiModificatorExtractions() {
		$this->eiModificatorExtractions = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getEiModificatorExtractionGroups() as $typePathStr => $eiModificatorExtractions) {
				if (!isset($this->eiModificatorExtractions[$typePathStr])) {
					$this->eiModificatorExtractions[$typePathStr] = array();
				}
				
				foreach ($eiModificatorExtractions as $eiModificatorExtraction) {
					$id = $eiModificatorExtraction->getId();
					
					if (isset($this->eiModificatorExtractions[$eiTypePathStr][$id])) {
						throw new $this->createDuplicatedEiModificatorIdException($eiTypePathStr, $id);
					}
					
					$eiTypePath = $eiModificatorExtraction->getTypePath();
					
					if (isset($this->customTypeExtractions[$eiTypePath->getTypeId()])) {
						throw new InvalidConfigurationException('Invalid configuration in: ' . $specCsDec->getDataSource(), 0, 
								new InvalidEiMaskConfigurationException('EiModificator with id \'' . $eiModificatorId 
										. '\' was configured not for CustomType \'' . $eiTypeId . '\.'));
					}
						
					$this->eiModificatorExtractions[$eiTypePathStr][$id] = $eiModificatorExtraction;
				}
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
		
		return new InvalidConfigurationException('EiType for entity class \'' . $entityClassName 
				. '\' is defined in multiple times in: ' . implode(', ', $configSources));
	}
	
	private function createDuplicatedEiMaskIdException(string $eiTypeId, string $eiMaskId): InvalidConfigurationException {
		$dataSources = array();
		foreach ($this->specCsDecs as $specConfig) {
			if ($specConfig === null) continue;
			
			if ($specConfig->containsEiMaskId($eiTypeId, $eiMaskId)) {
				$dataSources[] = $specConfig->getDataSource();
			}
		}
		
		return new InvalidConfigurationException('EiMask with id \'' . $eiMaskId 
				. '\' for EiType \'' . $eiTypeId . '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
	}
	
	private function createDuplicatedEiModificatorIdException(string $eiTypeId, string $eiModificatorId): InvalidConfigurationException {
		$dataSources = array();
		foreach ($this->specCsDecs as $specConfig) {
			if ($specConfig === null) continue;
			
			if ($specConfig->containsEiModificatorId($eiTypeId, $eiModificatorId)) {
				$dataSources[] = $specConfig->getDataSource();
			}
		}
		
		return new InvalidConfigurationException('EiModificator with id \'' . $eiModificatorId 
				. '\' for EiType \'' . $eiTypeId . '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
	}
	
// 	private function createDuplicatedMenuItemIdException($menuItemId): InvalidConfigurationException {
// 		$dataSources = array();
// 		foreach ($this->specCsDecs as $specConfig) {
// 			if ($specConfig->containsMenuItemId($menuItemId)) {
// 				$dataSources[] = $specConfig->getDataSource();
// 			}
// 		}
	
// 		throw new InvalidConfigurationException('MenuItem with id \'' . $menuItemId
// 				. '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
// 	}
	
	/**
	 * @return string[]
	 */
	public function getTypeIds(): array {
		return array_merge(array_keys($this->customTypeExtractions), array_keys($this->eiTypeExtractions));
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsTypeId(string $id) {
		return isset($this->customTypeExtractions[$id]) || isset($this->eiTypeExtractions[$id]);
	}
	
// 	/**
// 	 * @return TypeExtraction[] 
// 	 */
// 	public function getTypeExtractions(): array {
// 		return $this->specExtractions;
// 	}
	
// 	/**
// 	 * @param string $id
// 	 * @throws UnknownSpecException
// 	 * @return TypeExtraction
// 	 */
// 	public function getTypeExtractionById($id) {
// 		if (isset($this->specExtractions[$id])) {
// 			return $this->specExtractions[$id];
// 		}
		
// 		throw new UnknownSpecException('No Spec with id \'' . $id . '\' defined in: ' 
// 				. $this->buildConfigSourceString());
// 	}
	
	public function containsEiTypeEntityClassName(string $className): bool {
		return isset($this->eiTypeExtractionCis[$className]);
	}
	
	public function getEiTypeExtractionByClassName($className): EiTypeExtraction {
		if (isset($this->eiTypeExtractionCis[$className])) {
			return $this->eiTypeExtractionCis[$className];
		}
		
		throw new UnknownSpecException('No EiType for Entity \'' . $className . '\' defined in: ' 
				. $this->buildConfigSourceString());
	}
	
	/**
	 * @return CustomTypeExtraction[]
	 */
	public function getCustomTypeExtractions() {
		return $this->customTypeExtractions;
	}
	
	/**
	 * @return EiTypeExtraction[]
	 */
	public function getEiTypeExtractions() {
		return $this->eiTypeExtractions;
	}
	
	/**
	 * @return array
	 */
	public function getEiTypeExtensionExtractionGroups() {
		return $this->eiTypeExtensionExtractionGroups;
	}
	
	/**
	 * @return array
	 */
	public function getEiModificatorExtractionGroups() {
		return $this->eiModificatorExtractions;
	}
	
	
	/**
	 * @param string $typeId
	 * @return EiTypeExtensionExtraction[]
	 */
	public function getEiTypeExtensionExtractionsByEiTypeId(string $typeId) {
		if (isset($this->eiTypeExtensionExtractionGroups[$typeId])) {
			return $this->eiTypeExtensionExtractionGroups[$typeId];
		}
		return array();
	}
		
	/**
	 * @param string $typeId
	 * @return EiModificatorExtraction[]
	 */
	public function getEiModificatorExtractionsByEiTypeId(string $typeId) {
		if (isset($this->eiModificatorExtractions[$typeId])) {
			return $this->eiModificatorExtractions[$typeId];
		}
		return array();
	}
	
	/**
	 * @param TypePath $typePath
	 * @return MenuItemExtraction[]
	 */
	public function getMenuItemExtractionByTypePath(TypePath $typePath) {
		$typePathStr = (string) $typePath;
		if (isset($this->menuItemExtractions[$typePathStr])) {
			return $this->menuItemExtractions[$typePathStr];
		}
		return null;
	}
	
	/**
	 * @return string
	 */
	private function buildConfigSourceString() {
		$configSourceStrs = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$configSourceStrs[] = (string) $specCsDec->getConfigSource();
		}
		
		return 'config source bundle (' . implode(', ', $configSourceStrs) . ')';
	}
	
	public function addSpec(TypeExtraction $specExtraction) {
		$id = $specExtraction->getId();
		if (isset($this->specExtractions[$id])) {
			throw new IllegalStateException('Duplicated spec id: ' . $id);
		}
		
		$this->specExtractions[$id] = $specExtraction;
		
		if ($specExtraction instanceof EiTypeExtraction) {
			$entityClassName = $specExtraction->getEntityClassName();
			
			if (isset($this->eiTypeExtractions[$entityClassName])) {
				throw new IllegalStateException('EiType for Entity already defined: ' . $entityClassName);
			}
			
			$this->eiTypeExtractions[$entityClassName] = $specExtraction;
		} else {
			ArgUtils::assertTrue($specExtraction instanceof CustomTypeExtraction);
		}
		
		unset($this->eiTypeExtensionExtractionGroups[$specExtraction->getId()]);
	}
	
	public function removeSpecById(string $specId) {
		if (isset($this->specExtractions[$specId]) && $this->specExtractions[$specId] instanceof EiTypeExtraction) {
			unset($this->eiTypeExtractions[$this->specExtractions[$specId]->getEntityClassName()]);
		}
		
		unset($this->specExtractions[$specId]);
		unset($this->eiTypeExtensionExtractionGroups[$specId]);
	}
	
	public function getMenuItemExtractions() {
		return $this->menuItemExtractions;
	}
	
	public function getMenuItemExtractionById(string $id): MenuItemExtraction {
		if (isset($this->menuItemExtractions[$id])) {
			return $this->menuItemExtractions[$id];
		}
		throw new UnknownMenuItemException('No MenuItem with id \'' . $id . '\' defined in: '
				. $this->buildConfigSourceString(), null, null, 2);
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
					$this->modularConfigSource->getOrCreateConfigSourceByModuleNamespace($moduleNamespace), $moduleNamespace);
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
					->addTypeExtraction($specExtraction);
						
			if ($specExtraction instanceof EiTypeExtraction) {
				foreach ($specExtraction->getEiTypeExtensionExtractions() as $eiMaskExtensionExtraction) {
					$this->getSpecCsDescByModuleNamespace($eiMaskExtensionExtraction->getModuleNamespace())
							->addEiTypeExtensionExtraction($specId, $eiMaskExtensionExtraction);
				}
				
				foreach ($specExtraction->getEiModificatorExtractions() as $eiModificatorExtraction) {
					$this->getSpecCsDescByModuleNamespace($eiModificatorExtraction->getModuleNamespace())
							->addEiModificatorExtraction($specId, $eiModificatorExtraction);
				}
			}
		}
		
		foreach ($this->eiTypeExtensionExtractionGroups as $eiTypeId => $eiMaskExtensionExtractions) {
			foreach ($eiMaskExtensionExtractions as $eiMaskExtensionExtraction) {
				$this->getSpecCsDescByModuleNamespace($eiMaskExtensionExtraction->getModuleNamespace())
						->addEiTypeExtensionExtraction($eiTypeId, $eiMaskExtensionExtraction);
			}
		}
		
		foreach ($this->eiModificatorExtractions as $eiTypeId => $unboundEiModificatorExtractions) {
			foreach ($unboundEiModificatorExtractions as $unboundEiModificatorExtractions) {
				$this->getSpecCsDescByModuleNamespace($unboundEiModificatorExtractions->getModuleNamespace())
						->addEiModificatorExtraction($eiTypeId, $unboundEiModificatorExtractions);
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
	
	
	
	private function dingselMenuItemExtractions() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getMenuItemExtractions() as $menuItemExtraction) {
				$typePathStr = (string) $menuItemExtraction->getTypePath();
				
				if (isset($this->menuItemExtractions[$typePathStr])) {
					throw $this->createDuplicatedMenuItemIdException($typePathStr);
				}
				
				$this->menuItemExtractions[$typePathStr] = $menuItemExtraction;
			}
		}
	}
}
