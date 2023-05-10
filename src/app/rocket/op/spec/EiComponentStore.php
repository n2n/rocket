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
namespace rocket\op\spec;

use n2n\reflection\ReflectionUtils;
use n2n\core\TypeNotFoundException;
use rocket\op\spec\source\ModularConfigSource;
use rocket\op\ei\component\prop\indepenent\IndependentEiProp;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\component\command\IndependentEiCmd;
use rocket\op\ei\component\modificator\EiModNature;
use rocket\op\ei\component\modificator\IndependentEiModNature;

class EiComponentStore {
	const EI_FIELD_CLASSES_KEY = 'eiPropClasses';
	const EI_COMMAND_CLASSES_KEY = 'eiCmdClasses';
	const EI_COMMAND_GROUPS_KEY = 'eiCmdGroups';
	const EI_MODIFICATOR_CLASSES_KEY = 'eiModificatorClasses';
	
	private $eiComponentConfigSource;
	private $eiPropClasses = array();
	private $eiPropClassesByModule = array();
	private $eiCmdClasses = array();
	private $eiCmdClassesByModule = array();
	private $eiCmdGroups = array();
	private $eiCmdGroupsByModule = array();
	private $eiModificatorClasses = array();
	private $eiModificatorClassesByModule = array();
	
	public function __construct(ModularConfigSource $eiComponentConfigSource, array $moduleNamespaces) {
		$this->eiComponentConfigSource = $eiComponentConfigSource;
		
		foreach ($moduleNamespaces as $moduleNamespace) {
			if ($this->eiComponentConfigSource->containsModuleNamespace($moduleNamespace)) {
				$this->analyzeModuleRawData($moduleNamespace, $this->eiComponentConfigSource
						->getOrCreateConfigSourceByModuleNamespace($moduleNamespace)->readArray());
			}
		}
	}
	
	private function extractElementArray($elementKey, array $rawData) {
		if (isset($rawData[$elementKey]) && is_array($rawData[$elementKey])) {
			return $rawData[$elementKey];
		}
		
		return array();
	}
	
	private function analyzeModuleRawData(string $moduleNamespace, array $moduleRawData) {		
		// EiProps
		$this->eiPropClassesByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_FIELD_CLASSES_KEY, $moduleRawData) 
				as $key => $eiPropClassName) {
			try {
				$fieldClass = ReflectionUtils::createReflectionClass($eiPropClassName);
				if (!$fieldClass->implementsInterface(EiPropNature::class)
						|| !$fieldClass->implementsInterface(IndependentEiProp::class)) continue;
				
				$this->eiPropClasses[$eiPropClassName] = $fieldClass;
				$this->eiPropClassesByModule[$moduleNamespace][$eiPropClassName] = $fieldClass;
			} catch (\ReflectionException $e) { }
		}
		
		// EiCommands
		$this->eiCmdClassesByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_COMMAND_CLASSES_KEY, $moduleRawData) 
				as $key => $eiCmdClassName) {
			try {
				$eiCmdClass =  ReflectionUtils::createReflectionClass($eiCmdClassName);
				if (!$eiCmdClass->implementsInterface(EiCmdNature::class)
						|| !$eiCmdClass->implementsInterface(IndependentEiCmd::class)) continue;
				
				$this->eiCmdClasses[$eiCmdClassName] = $eiCmdClass;
				$this->eiCmdClassesByModule[$moduleNamespace][$eiCmdClassName] = $eiCmdClass;
			} catch (\ReflectionException $e) { }
		}
				
		// EiCommandGroups
		$this->eiCmdGroupsByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_COMMAND_GROUPS_KEY, $moduleRawData) 
				as $groupName => $eiCmdClassNames) {
			if (!is_array($eiCmdClassNames)) {
				continue;
			}
		
			$eiCmdGroup = new EiCommandGroup($groupName);
			foreach ($eiCmdClassNames as $key => $eiCmdClassName) {
				if (!isset($this->eiCmdClasses[$eiCmdClassName])) continue;
				$eiCmdGroup->addEiCommandClass($this->eiCmdClasses[$eiCmdClassName]); 
			}
		
			$this->eiCmdGroups[$groupName] = $eiCmdGroup;
			$this->eiCmdGroupsByModule[$moduleNamespace][$groupName] = $eiCmdGroup;
		}
		
		// EiModificators
		$this->eiModificatorClassesByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_MODIFICATOR_CLASSES_KEY, $moduleRawData) 
				as $key => $eiModificatorClassName) {
			try {
				$constraintClass =  ReflectionUtils::createReflectionClass($eiModificatorClassName);
				if (!$constraintClass->implementsInterface(EiModNature::class)
						|| !$constraintClass->implementsInterface(IndependentEiModNature::class)) continue;
		
				$this->eiModificatorClasses[$eiModificatorClassName] = $constraintClass;
				$this->eiModificatorClassesByModule[$moduleNamespace][$eiModificatorClassName] = $constraintClass;
			} catch (\ReflectionException $e) { }
		}
	}
	
	/**
	 * @return \ReflectionClass[]
	 */
	public function getEiPropClasses(): array {
		return $this->eiPropClasses;
	}
	
	public function getEiPropClassesByModuleNamespace(string $moduleNamespace): array {
		if (isset($this->eiPropClassesByModule[$moduleNamespace])) {
			return $this->eiPropClassesByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiPropClassesByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiPropClassesByModule[$moduleNamespace])) return;
		foreach ($this->eiPropClassesByModule[$moduleNamespace] as $eiPropClass) {
			unset($this->eiPropClasses[$eiPropClass->getName()]);
		}
		$this->eiPropClassesByModule[$moduleNamespace] = array();
	}
	
	public function addEiPropClass($moduleNamespace, \ReflectionClass $eiPropClass) {
		if (!isset($this->eiPropClassesByModule[$moduleNamespace])) {
			$this->eiPropClassesByModule[$moduleNamespace] = array();
		}
		$className = $eiPropClass->getName();
		$this->eiPropClasses[$className] = $eiPropClass;
		$this->eiPropClassesByModule[$moduleNamespace][$className] = $eiPropClass;
	}
	
	public function getEiCommandClasses() {
		return $this->eiCmdClasses;
	}
	
	public function getEiCommandClassesByModuleNamespace(string $moduleNamespace) {
		if (isset($this->eiCmdClassesByModule[$moduleNamespace])) {
			return $this->eiCmdClassesByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiCommandClassesByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiCmdClassesByModule[$moduleNamespace])) return;
		foreach ($this->eiCmdClassesByModule[$moduleNamespace] as $eiCmdClass) {
			unset($this->eiCmdClasses[$eiCmdClass->getName()]);
		}
		$this->eiCmdClassesByModule[$moduleNamespace] = array();
	}
	
	public function addEiCommandClass($moduleNamespace, \ReflectionClass $eiCmdClass) {
		if (!isset($this->eiCmdClassesByModule[$moduleNamespace])) {
			$this->eiCmdClassesByModule[$moduleNamespace] = array();
		}
		$className = $eiCmdClass->getName();
		$this->eiCmdClasses[$className] = $eiCmdClass;
		$this->eiCmdClassesByModule[$moduleNamespace][$className] = $eiCmdClass;
	}
	
	/**
	 * @return EiCommandGroup
	 */
	public function getEiCommandGroups() {
		return $this->eiCmdGroups;
	}
	
	public function getEiCommandGroupsByModuleNamespace(string $moduleNamespace) {
		if (isset($this->eiCmdGroupsByModule[$moduleNamespace])) {
			return $this->eiCmdGroupsByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiCommandGroupsByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiCmdGroupsByModule[$moduleNamespace])) return;
		foreach ($this->eiCmdGroupsByModule[$moduleNamespace] as $eiCmdGroup) {
			unset($this->eiCmdGroups[$eiCmdGroup->getName()]);
		}
		$this->eiCmdGroupsByModule[$moduleNamespace] = array();
	}
	
	public function addEiCommandGroup($moduleNamespace, EiCommandGroup $eiCmdGroup) {
		if (!isset($this->eiCmdGroupsByModule[$moduleNamespace])) {
			$this->eiCmdGroupsByModule[$moduleNamespace] = array();
		}
		
		$className = $eiCmdGroup->getName();
		$this->eiCmdGroups[$className] = $eiCmdGroup;
		$this->eiCmdGroupsByModule[$moduleNamespace][$className] = $eiCmdGroup;
	}
	
	public function getEiModificatorClasses() {
		return $this->eiModificatorClasses;
	}
	
	public function getEiModificatorClassesByModuleNamespace(string $moduleNamespace) {
		if (isset($this->eiModificatorClassesByModule[$moduleNamespace])) {
			return $this->eiModificatorClassesByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiModificatorClassesByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiModificatorClassesByModule[$moduleNamespace])) return;
		foreach ($this->eiModificatorClassesByModule[$moduleNamespace] as $eiModificatorClass) {
			unset($this->eiModificatorClasses[$eiModificatorClass->getName()]);
		}
		$this->eiModificatorClassesByModule[$moduleNamespace] = array();
	}
	
	public function addEiModificatorClass($moduleNamespace, \ReflectionClass $eiModificatorClass) {
		if (!isset($this->eiModificatorClassesByModule[$moduleNamespace])) {
			$this->eiModificatorClassesByModule[$moduleNamespace] = array();
		}
		$className = $eiModificatorClass->getName();
		$this->eiModificatorClasses[$className] = $eiModificatorClass;
		$this->eiModificatorClassesByModule[$moduleNamespace][$className] = $eiModificatorClass;
	}
	
	public function flush($module = null) {
		if ($module !== null) {
			$this->persistByModule((string) $module, $this->configSources[(string) $module]);
			return;
		}
		
		$moduleNamespaces = array_unique(array_merge(
				array_keys($this->eiPropClasses), array_keys($this->eiPropClassesByModule), 
				array_keys($this->eiCmdClasses), array_keys($this->eiCmdClassesByModule), 
				array_keys($this->eiCmdGroups), array_keys($this->eiCmdGroupsByModule), 
				array_keys($this->eiModificatorClasses), array_keys($this->eiModificatorClassesByModule)));
		
		foreach ($moduleNamespaces as $moduleNamespace) {
			$this->persistByModule($moduleNamespace);
		}
	}
	
	private function persistByModule(string $moduleNamespace) {
		$write = false;
		$moduleRawData = array();
		
		if (isset($this->eiPropClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_FIELD_CLASSES_KEY] = array();
			foreach ($this->eiPropClassesByModule[$moduleNamespace] as $eiPropClass) {
				$moduleRawData[self::EI_FIELD_CLASSES_KEY][] = $eiPropClass->getName();
			} 
		}
		
		if (isset($this->eiCmdClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_COMMAND_CLASSES_KEY] = array();
			foreach ($this->eiCmdClassesByModule[$moduleNamespace] as $eiCmdClass) {
				$moduleRawData[self::EI_COMMAND_CLASSES_KEY][] = $eiCmdClass->getName();
			}
		}
		
		if (isset($this->eiCmdGroupsByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_COMMAND_GROUPS_KEY] = array();
			foreach ($this->eiCmdGroupsByModule[$moduleNamespace] as $eiCmdGroup) {
				$groupName = $eiCmdGroup->getName();
				$moduleRawData[self::EI_COMMAND_GROUPS_KEY][$groupName] = array();
				foreach ($eiCmdGroup->getEiCommandClasses() as $eiCmdClass) {
					$moduleRawData[self::EI_COMMAND_GROUPS_KEY][$groupName][] = $eiCmdClass->getName();
				}
			}
		}
		
		if (isset($this->eiModificatorClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_MODIFICATOR_CLASSES_KEY] = array();
			foreach ($this->eiModificatorClassesByModule[$moduleNamespace] as $eiModificatorClass) {
				$moduleRawData[self::EI_MODIFICATOR_CLASSES_KEY][] = $eiModificatorClass->getName();
			} 
		}
		
		if (isset($this->listenerClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::SPEC_LISTENER_CLASSES_KEY] = array();
			foreach ($this->listenerClassesByModule[$moduleNamespace] as $listenerClass) {
				$moduleRawData[self::SPEC_LISTENER_CLASSES_KEY][] = $listenerClass->getName();
			} 
		}
		
		if ($write) {
			$this->eiComponentConfigSource->getConfigSourceByModule($moduleNamespace)->writeArray($moduleRawData);
		}
	}
}
