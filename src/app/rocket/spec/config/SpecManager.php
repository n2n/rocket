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
namespace rocket\spec\config;

use rocket\spec\ei\EiType;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\spec\config\extr\SpecExtraction;
use rocket\spec\ei\component\EiConfigurator;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\spec\config\extr\SpecExtractionManager;
use rocket\core\model\MenuItem;
use rocket\core\model\UnknownMenuItemException;
use rocket\spec\config\extr\MenuItemExtraction;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\mask\UnknownEiMaskException;
use rocket\spec\config\extr\CustomSpecExtraction;
use n2n\util\config\AttributesException;
use rocket\core\model\Rocket;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\orm\model\UnknownEntityPropertyException;
use rocket\spec\ei\component\prop\indepenent\PropertyAssignation;
use rocket\spec\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\util\config\InvalidConfigurationException;
use n2n\core\N2N;
use n2n\core\TypeNotFoundException;
use n2n\persistence\orm\OrmConfigurationException;

class SpecManager {	
	private $rocketConfigSource;	
	private $entityModelManager;
	
	private $manageConfig;
	private $specExtractionManager;
	
	private $menuItems = array();
	private $specs = array();
	private $eiTypes = array();
	
	
// 	private $scriptComponentsStorage;
	
// 	private $scriptsConfigSource;
// 	private $entityModelManager;
	private $exclusiveMode = false;
	private $eiTypeSetupQueue;
	
// 	private $scripts = array();
// 	private $eiTypes = array();
// 	

// 	private $manageConfig;
// 	private $specConfigs = array();
// 	

	public function __construct(SpecExtractionManager $specExtractionManager, EntityModelManager $entityModelManager) {
		$this->specExtractionManager = $specExtractionManager;
		$this->entityModelManager = $entityModelManager;
		$this->eiTypeSetupQueue = new EiTypeSetupQueue($this);
	}
	
	/**
	 * @return \n2n\persistence\orm\model\EntityModelManager
	 */
	public function getEntityModelManager() {
		return $this->entityModelManager;
	}
	
	/**
	 * @return \rocket\spec\config\extr\SpecExtractionManager
	 */
	public function getSpecExtractionManager() {
		if (!$this->specExtractionManager->isInitialized()) {
			$this->specExtractionManager->initialize();
		}
		
		return $this->specExtractionManager;
	}

	/**
	 * @param string $id
	 * @return MenuItem
	 * @throws UnknownMenuItemException
	 */
	public function getMenuItemById(string $id): MenuItem {
		if (isset($this->menuItems[$id])) {
			return $this->menuItems[$id];
		}
		
		$this->specExtractionManager->getMenuItemExtractionById($id);
		throw new IllegalStateException();
		
// 		try {
// 			return $this->menuItems[$id] = $this->createMenuItem($this->specExtractionManager
// 					->getMenuItemExtractionById($id));
// 		} catch (UnknownSpecException $e) {
// 			throw $this->createInvalidMenuItemConfigurationException($id, $e);
// 		} catch (UnknownEiMaskException $e)  {
// 			throw $this->createInvalidMenuItemConfigurationException($id, $e);
// 		}
		
	}
	
	private function createInvalidMenuItemConfigurationException($menuItemId, \Exception $previous) {
		throw new InvalidMenuItemConfigurationException('MenuItem with following id invalid configured: ' 
				. $menuItemId, 0, $previous);
	}
	
	private function createMenuItem(MenuItemExtraction $menuItemExtraction) {
		$specId = $menuItemExtraction->getSpecId();
		if (!isset($this->specs[$specId])) {
			throw new UnknownSpecException('MenuItem is assigned to unknown Spec: ' . $specId );
		}
		
		$spec = $this->specs[$specId];
		$eiMaskId = $menuItemExtraction->getEiMaskId();
		if ($spec instanceof CustomSpec) {
			if (null === $eiMaskId) {
				return new CustomMenuItem($menuItemExtraction->getId(), $spec, $menuItemExtraction->getLabel());
			}
			
			throw $this->createInvalidMenuItemConfigurationException($menuItemExtraction->getId(), 
					new InvalidMenuItemConfigurationException('EiMask (id: \'' . $eiMaskId 
							. '\') configured for CustomSpec (id: \'' . $spec->getId() . '\''));
		}
		
		$eiMask = null;
		if ($eiMaskId !== null) {
			$eiMask = $spec->getEiMaskCollection()->getById($eiMaskId);
		} else {
			$eiMask = $spec->getEiMaskCollection()->getOrCreateDefault();
		}
		
		return new EiMenuItem($menuItemExtraction->getId(), $spec, $eiMask, $menuItemExtraction->getLabel());
	}
	
// 	public function containsSpecId($id) {
// 		foreach ($this->specConfigs as $scriptConfig) {
// 			if ($scriptConfig->containsScriptId($id)) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
					
// 	public function containsEiTypeClass(\ReflectionClass $class) {
// 		foreach ($this->specConfigs as $scriptConfig) {
// 			if ($scriptConfig->containsEiTypeClass($class)) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
	
	public function initialize(N2nContext $n2nContext, bool $exclusiveMode = false) {
		$this->exclusiveMode = $exclusiveMode;
		$cacheStore = $n2nContext->getAppCache()->lookupCacheStore(SpecManager::class);
		$this->specExtractionManager->load();
		$charcs = null;
		if (!N2N::isDevelopmentModeOn() && null !== ($hashCode = $this->specExtractionManager->getModularConfigSource()->hashCode())) {
			$charcs = array('version' => Rocket::VERSION, 'hashCode' => $hashCode);
		}
		
		if ($charcs !== null && null !== ($cacheItem = $cacheStore->get(SpecManager::class, $charcs))) {
// 			$data = $cacheItem->getData();
			$this->specs = $cacheItem->data['specs'];
			$this->eiTypes = $cacheItem->data['eiTypes'];
			$this->menuItems = $cacheItem->data['menuItems'];
			$this->eiTypeSetupQueue->setPropIns($cacheItem->data['propIns']);
			$this->eiTypeSetupQueue->setEiConfigurators($cacheItem->data['eiConfigurators']);
		} else {
			if (!$this->specExtractionManager->isInitialized()) {
				$this->specExtractionManager->initialize();
			}
			
			foreach ($this->specExtractionManager->getSpecExtractions() as $specExtraction) {
				$this->createSpecFromExtr($specExtraction);
			}
			
			foreach ($this->specExtractionManager->getMenuItemExtractions() as $id => $menuItemExtraction) {
				try {
					$this->menuItems[$id] = $this->createMenuItem($menuItemExtraction);
				} catch (UnknownSpecException $e) {
					throw $this->createInvalidMenuItemConfigurationException($id, $e);
				} catch (UnknownEiMaskException $e)  {
					throw $this->createInvalidMenuItemConfigurationException($id, $e);
				}
			}
			
			if ($charcs !== null) {
				$cacheStore->store(SpecManager::class, $charcs, array(
						'specs' => $this->specs, 'eiTypes' => $this->eiTypes, 'menuItems' => $this->menuItems,
						'propIns' => $this->eiTypeSetupQueue->getPropIns(),
						'eiConfigurators' => $this->eiTypeSetupQueue->getEiConfigurators()));
			}
		}
		
		foreach ($this->eiTypes as $className => $eiType) {
			$entityModel = null;
			try {
				$class = ReflectionUtils::createReflectionClass($className);
				$entityModel = $this->entityModelManager->getEntityModelByClass($class);
			} catch (TypeNotFoundException $e) {
				if ($this->exclusiveMode) continue;
				
				throw new InvalidSpecConfigurationException('Invalid EiType: ' . $eiType, 0, $e);
			} catch (OrmConfigurationException $e) {
				if ($this->exclusiveMode) continue;
				
				throw new InvalidSpecConfigurationException('Invalid EiType: ' . $eiType, 0, $e);
			}
			
			
			$eiType->setEntityModel($entityModel);
			
			if ($eiType->getEntityModel()->hasSuperEntityModel()) {
				$superClassName = $eiType->getEntityModel()->getSuperEntityModel()->getClass()->getName();
				if (!isset($this->eiTypes[$superClassName])) {
					throw new InvalidConfigurationException('EiType required for ' . $superClassName);
				}
				
				$eiType->setSuperEiType($this->eiTypes[$superClassName]);
			}
		}
		
		if (!$this->exclusiveMode) {
			$this->eiTypeSetupQueue->trigger($n2nContext);
		}
	}
	
	private function createSpecFromExtr(SpecExtraction $specExtraction) {
		if ($specExtraction instanceof CustomSpecExtraction) {
			return $this->specs[$specExtraction->getId()] = CustomSpecFactory::create($specExtraction);
		} 
		
		$eiType = (new EiTypeFactory($this->entityModelManager, $this->getEiTypeSetupQueue()))
				->create($specExtraction);
				
		$this->specs[$specExtraction->getId()] = $eiType;
		$this->eiTypes[$specExtraction->getEntityClassName()] = $eiType;
			
		return $eiType;
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\spec\ei\EiType
	 */
	public function getEiTypeByClass(\ReflectionClass $class) {
		return $this->getEiTypeByClassName($class->getName());
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\spec\ei\EiType
	 */
	public function getEiTypeByClassName(string $className) {
		if (isset($this->eiTypes[$className])) {
			if ($this->exclusiveMode) {
				$this->eiTypeSetupQueue->exclusivePropInForEiType($this->eiTypes[$className]->getId());
			}
			
			return $this->eiTypes[$className];
		}
		
		$this->specExtractionManager->getEiTypeExtractionByClassName($className);
		throw new IllegalStateException('SpecManager not initialized.');
	}
	
	/**
	 *
	 * @param string $id
	 * @return Spec
	 * @throws UnknownSpecException
	 * @throws InvalidSpecConfigurationException
	 */
	public function getSpecById($id): Spec {
		if (isset($this->specs[$id])) {
			if ($this->exclusiveMode) {
				$this->eiTypeSetupQueue->exclusivePropInForEiType($this->specs[$id]->getId());
			}
			
			return $this->specs[$id];
		}
		
		$this->specExtractionManager->getSpecExtractionById($id);
		throw new IllegalStateException('SpecManager not initialized.');
	}
	
	public function containsEiTypeClass(\ReflectionClass $class) {
		return isset($this->eiTypes[$class->getName()]);
	}
	
	public function containsEiTypeClassName(string $className) {
		return isset($this->eiTypes[$className]);
	}
	
	public function getEiTypes(): array {
		if ($this->exclusiveMode) {
			$this->eiTypeSetupQueue->propIns();
		}
		return $this->eiTypes;
	}
	
	public function getCustomSpecs(): array {
		$customSpecs = array();
		foreach ($this->specs as $spec) {
			if ($spec instanceof CustomSpec) {
				$customSpecs[$spec] = $spec;
			}
		}
		return $customSpecs;
	}
	
	/**
	 * @param string $id
	 * @throws UnknownSpecException
	 * @throws InvalidSpecConfigurationException
	 * @return EiType
	 */
	public function getEiTypeById(string $id) {
		$script = $this->getSpecById($id);
		if ($script instanceof EiType) {
			if ($this->exclusiveMode) {
				$this->eiTypeSetupQueue->exclusivePropInForEiType($script->getId());
			}
			return $script;
		}
	
		throw new UnknownSpecException('Script with id  \'' . $id . '\' is no EiType');
	}

	public function getSpecs(): array {
		return $this->specs;
	}
	
	public function removeSpecById($id) {
		if (isset($this->specs[$id])) {
			if ($this->specs[$id] instanceof EiType) {
				unset($this->eiTypes[$this->specs[$id]->getEntityModel()->getClass()->getName()]);
			}
			
			unset($this->specs[$id]);
		}
		
		$this->specExtractionManager->removeSpecById($id);
	}
	
	public function flush() {
		throw new NotYetImplementedException();
// 		$this->specExtractionManager->clear();
		
// 		foreach ($this->specs as $spec) {
// 			$scriptConfig = $this->getOrCreateSpecConfig($script->getModule());
// 			$scriptConfig->putSpecExtraction($script->toSpecExtraction());
// 		}
		
// 		foreach ($this->specConfigs as $scriptConfig) {
// 			$scriptConfig->flush();
// 		}
	}
	
	public function getEiTypeSetupQueue() {
		return $this->eiTypeSetupQueue;
	}
}

class EiTypeSetupQueue {
	private $specManager;
	private $propIns = array();
	private $eiConfigurators = array();
	private $es = array();
	
	public function __construct(SpecManager $specManager) {
		$this->specManager = $specManager;
	}
	
// 	public function isLenient() {
// 		return $this->lenient;
// 	}
	
// 	public function setLenient($lenient) {
// 		$this->lenient = (boolean) $lenient;
// 	}
		
	public function addPropIn(PropIn $closure) {
		$this->propIns[] = $closure;
	}
	
	public function add(EiConfigurator $eiConfigurator) {
		$this->eiConfigurators[] = $eiConfigurator;
	}
	
	public function getPropIns() {
		return $this->propIns;
	}
	
	public function setPropIns(array $propIns) {
		$this->propIns = $propIns;
	}
	
	public function getEiConfigurators() {
		return $this->eiConfigurators;
	}
	
	public function setEiConfigurators(array $eiConfigurators) {
		$this->eiConfigurators = $eiConfigurators;
	}
		
	public function trigger(N2nContext $n2nContext) {
		$this->propIns();
		
		while (null !== ($eiConfigurator = array_shift($this->eiConfigurators))) {
			$this->setup($n2nContext, $eiConfigurator);
			
// 			$eiTypeId = $eiComponent->getEiType()->getId();
// 			if (!isset($this->es[$eiTypeId])) {
// 				$this->es[$eiTypeId] = array();
// 			}
// 			$this->es[$eiTypeId][] = $e;
		}
	}

	public function exclusiveTriggerForEiType($eiTypeId, N2nContext $n2nContext) {
		$this->exclusivePropInForEiType($eiTypeId, $n2nContext);
	
		foreach ($this->eiConfigurators as $key => $eiConfigurator) {
			if ($eiConfigurator->getEiComponent()->getEiEngine()->getEiType()->getId() !== $eiTypeId) {
				continue;
			}
				
			$this->setup($n2nContext, $eiConfigurator);
			unset($this->eiConfigurators[$key]);
		}
	}
	
	private function setup($n2nContext, $eiConfigurator) {
		$eiComponent = $eiConfigurator->getEiComponent();
		$eiSetupProcess = new SpecEiSetupProcess($this->specManager, $n2nContext, $eiComponent);
		try {
			$eiConfigurator->setup($eiSetupProcess);
		} catch (AttributesException $e) {
			throw $eiSetupProcess->createException(null, $e);
		}
	}
	
	public function propIns() {
		while (null !== ($propIns = array_shift($this->propIns))) {
			$propIns->invoke();
		}
	}
	
	public function exclusivePropInForEiType($eiTypeId) {
		foreach ($this->propIns as $key => $propIn) {
			if ($propIn->getEiType()->getId() !== $eiTypeId) {
				continue;
			}
		
			$propIn->invoke();
			unset($this->propIns[$key]);
		}
	}
	
	
// 	public function buildErrorMessages($eiTypeId) {
// 		$errorMessages = array();
// 		if (isset($this->es[$eiTypeId])) {
// 			foreach ($this->es[$eiTypeId] as $e) {
// 				$errorMessages[] = new Message($e->getMessage());
// 			}
// 		}
// 		return $errorMessages;
// 	}
	
	

}

class PropIn {
	private $eiType;
	private $eiPropConfigurator;
	private $objectPropertyName;
	private $entityPropertyName;

	public function __construct($eiType, $eiPropConfigurator, $objectPropertyName, $entityPropertyName) {
		$this->eiType = $eiType;
		$this->eiPropConfigurator = $eiPropConfigurator;
		$this->objectPropertyName = $objectPropertyName;
		$this->entityPropertyName = $entityPropertyName;
	}

	public function getEiType() {
		return $this->eiType;
	}
	
	public function invoke() {
		$accessProxy = null;
		if (null !== $this->objectPropertyName) {
			try{
				$propertiesAnalyzer = new PropertiesAnalyzer($this->eiType->getEntityModel()->getClass(), false);
				$accessProxy = $propertiesAnalyzer->analyzeProperty($this->objectPropertyName, false, true);
				$accessProxy->setNullReturnAllowed(true);
			} catch (ReflectionException $e) {
				throw $this->createException(
						new InvalidEiComponentConfigurationException('EiProp is assigned to unknown property: '
								. $this->objectPropertyName, 0, $e));
			}
		}
			
		$entityProperty = null;
		if (null !== $this->entityPropertyName) {
			try {
				$entityProperty = $this->eiType->getEntityModel()->getLevelEntityPropertyByName($this->entityPropertyName, true);
			} catch (UnknownEntityPropertyException $e) {
				throw $this->createException(
						new InvalidEiComponentConfigurationException('EiProp is assigned to unknown EntityProperty: '
								. $this->entityPropertyName, 0, $e));
			}
		}

		if ($entityProperty !== null || $accessProxy !== null) {
			try {
				$this->eiPropConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
			} catch (IncompatiblePropertyException $e) {
				throw $this->createException($e);
			}
		}
	}
	
	private function createException($e) {
		$eiComponent = $this->eiPropConfigurator->getEiComponent();
		return new InvalidEiComponentConfigurationException('EiProp is invalid configured: ' . $eiComponent, 0, $e);
	}
}
