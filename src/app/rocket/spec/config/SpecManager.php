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

use rocket\spec\ei\EiSpec;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\spec\ei\component\field\EiProp;
use rocket\spec\config\extr\SpecExtraction;
use rocket\spec\config\source\RocketConfigSource;
use rocket\spec\ei\component\EiConfigurator;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\spec\config\Spec;
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
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\field\indepenent\IncompatiblePropertyException;
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
	private $eiSpecs = array();
	
	
// 	private $scriptComponentsStorage;
	
// 	private $scriptsConfigSource;
// 	private $entityModelManager;
	private $exclusiveMode = false;
	private $eiSpecSetupQueue;
	
// 	private $scripts = array();
// 	private $eiSpecs = array();
// 	

// 	private $manageConfig;
// 	private $specConfigs = array();
// 	

	public function __construct(SpecExtractionManager $specExtractionManager, EntityModelManager $entityModelManager) {
		$this->specExtractionManager = $specExtractionManager;
		$this->entityModelManager = $entityModelManager;
		$this->eiSpecSetupQueue = new EiSpecSetupQueue($this);
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
	 * @param unknown $id
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
					
// 	public function containsEiSpecClass(\ReflectionClass $class) {
// 		foreach ($this->specConfigs as $scriptConfig) {
// 			if ($scriptConfig->containsEiSpecClass($class)) {
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
			$this->eiSpecs = $cacheItem->data['eiSpecs'];
			$this->menuItems = $cacheItem->data['menuItems'];
			$this->eiSpecSetupQueue->setPropIns($cacheItem->data['propIns']);
			$this->eiSpecSetupQueue->setEiConfigurators($cacheItem->data['eiConfigurators']);
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
						'specs' => $this->specs, 'eiSpecs' => $this->eiSpecs, 'menuItems' => $this->menuItems,
						'propIns' => $this->eiSpecSetupQueue->getPropIns(),
						'eiConfigurators' => $this->eiSpecSetupQueue->getEiConfigurators()));
			}
		}
		
		foreach ($this->eiSpecs as $className => $eiSpec) {
			$entityModel = null;
			try {
				$class = ReflectionUtils::createReflectionClass($className);
				$entityModel = $this->entityModelManager->getEntityModelByClass($class);
			} catch (TypeNotFoundException $e) {
				if ($this->exclusiveMode) continue;
				
				throw new InvalidSpecConfigurationException('Invalid EiSpec: ' . $eiSpec, 0, $e);
			} catch (OrmConfigurationException $e) {
				if ($this->exclusiveMode) continue;
				
				throw new InvalidSpecConfigurationException('Invalid EiSpec: ' . $eiSpec, 0, $e);
			}
			
			
			$eiSpec->setEntityModel($entityModel);
			
			if ($eiSpec->getEntityModel()->hasSuperEntityModel()) {
				$superClassName = $eiSpec->getEntityModel()->getSuperEntityModel()->getClass()->getName();
				if (!isset($this->eiSpecs[$superClassName])) {
					throw new InvalidConfigurationException('EiSpec required for ' . $superClassName);
				}
				
				$eiSpec->setSuperEiSpec($this->eiSpecs[$superClassName]);
			}
		}
		
		if (!$this->exclusiveMode) {
			$this->eiSpecSetupQueue->trigger($n2nContext);
		}
	}
	
	private function createSpecFromExtr(SpecExtraction $specExtraction) {
		if ($specExtraction instanceof CustomSpecExtraction) {
			return $this->specs[$specExtraction->getId()] = CustomSpecFactory::create($specExtraction);
		} 
		
		$eiSpec = (new EiSpecFactory($this->entityModelManager, $this->getEiSpecSetupQueue()))
				->create($specExtraction);
				
		$this->specs[$specExtraction->getId()] = $eiSpec;
		$this->eiSpecs[$specExtraction->getEntityClassName()] = $eiSpec;
			
		return $eiSpec;
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpecByClass(\ReflectionClass $class) {
		$className = $class->getName();
		if (isset($this->eiSpecs[$className])) {
			if ($this->exclusiveMode) {
				$this->eiSpecSetupQueue->exclusivePropInForEiSpec($this->eiSpecs[$className]->getId());
			}
			
			return $this->eiSpecs[$className];
		}
		
		$this->specExtractionManager->getEiSpecExtractionByClassName($className);
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
				$this->eiSpecSetupQueue->exclusivePropInForEiSpec($this->specs[$id]->getId());
			}
			
			return $this->specs[$id];
		}
		
		$this->specExtractionManager->getSpecExtractionById($id);
		throw new IllegalStateException('SpecManager not initialized.');
	}
	
	public function containsEiSpecClass(\ReflectionClass $class) {
		return isset($this->eiSpecs[$class->getName()]);
	}
	
	public function getEiSpecs(): array {
		if ($this->exclusiveMode) {
			$this->eiSpecSetupQueue->propIns();
		}
		return $this->eiSpecs;
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
	 * @return EiSpec
	 */
	public function getEiSpecById($id) {
		$script = $this->getSpecById($id);
		if ($script instanceof EiSpec) {
			if ($this->exclusiveMode) {
				$this->eiSpecSetupQueue->exclusivePropInForEiSpec($script->getId());
			}
			return $script;
		}
	
		throw new UnknownSpecException('Script with id  \'' . $id . '\' is no EiSpec');
	}

	public function getSpecs(): array {
		return $this->specs;
	}
	
	public function removeSpecById($id) {
		if (isset($this->specs[$id])) {
			if ($this->specs[$id] instanceof EiSpec) {
				unset($this->eiSpecs[$this->specs[$id]->getEntityModel()->getClass()->getName()]);
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
	
	public function getEiSpecSetupQueue() {
		return $this->eiSpecSetupQueue;
	}
}

class EiSpecSetupQueue {
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
			
// 			$eiSpecId = $eiComponent->getEiSpec()->getId();
// 			if (!isset($this->es[$eiSpecId])) {
// 				$this->es[$eiSpecId] = array();
// 			}
// 			$this->es[$eiSpecId][] = $e;
		}
	}

	public function exclusiveTriggerForEiSpec($eiSpecId, N2nContext $n2nContext) {
		$this->exclusivePropInForEiSpec($eiSpecId, $n2nContext);
	
		foreach ($this->eiConfigurators as $key => $eiConfigurator) {
			if ($eiConfigurator->getEiComponent()->getEiEngine()->getEiSpec()->getId() !== $eiSpecId) {
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
	
	public function exclusivePropInForEiSpec($eiSpecId) {
		foreach ($this->propIns as $key => $propIn) {
			if ($propIn->getEiSpec()->getId() !== $eiSpecId) {
				continue;
			}
		
			$propIn->invoke();
			unset($this->propIns[$key]);
		}
	}
	
	
// 	public function buildErrorMessages($eiSpecId) {
// 		$errorMessages = array();
// 		if (isset($this->es[$eiSpecId])) {
// 			foreach ($this->es[$eiSpecId] as $e) {
// 				$errorMessages[] = new Message($e->getMessage());
// 			}
// 		}
// 		return $errorMessages;
// 	}
	
	

}

class PropIn {
	private $eiSpec;
	private $eiPropConfigurator;
	private $objectPropertyName;
	private $entityPropertyName;

	public function __construct($eiSpec, $eiPropConfigurator, $objectPropertyName, $entityPropertyName) {
		$this->eiSpec = $eiSpec;
		$this->eiPropConfigurator = $eiPropConfigurator;
		$this->objectPropertyName = $objectPropertyName;
		$this->entityPropertyName = $entityPropertyName;
	}

	public function getEiSpec() {
		return $this->eiSpec;
	}
	
	public function invoke() {
		$accessProxy = null;
		if (null !== $this->objectPropertyName) {
			try{
				$propertiesAnalyzer = new PropertiesAnalyzer($this->eiSpec->getEntityModel()->getClass(), false);
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
				$entityProperty = $this->eiSpec->getEntityModel()->getLevelEntityPropertyByName($this->entityPropertyName, true);
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