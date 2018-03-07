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
namespace rocket\spec;

use rocket\ei\EiType;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\ei\component\EiConfigurator;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\spec\extr\SpecExtractionManager;
use rocket\core\model\LaunchPad;
use rocket\core\model\UnknownLaunchPadException;
use rocket\spec\extr\CustomTypeExtraction;
use n2n\util\config\AttributesException;
use rocket\core\model\Rocket;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\orm\model\UnknownEntityPropertyException;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\util\config\InvalidConfigurationException;
use n2n\core\N2N;
use n2n\core\TypeNotFoundException;
use n2n\persistence\orm\OrmConfigurationException;
use rocket\spec\extr\EiTypeExtraction;
use rocket\ei\mask\EiMask;
use rocket\custom\CustomType;
use n2n\util\cache\CorruptedCacheStoreException;

class Spec {	
	private $rocketConfigSource;	
	private $entityModelManager;
	
	private $specExtractionManager;
	
	private $launchPads = array();
	private $customTypes = array();
	private $eiTypes = array();
	private $eiTypeCis = array();
	
	
// 	private $scriptComponentsStorage;
	
// 	private $scriptsConfigSource;
// 	private $entityModelManager;
	private $noSetupMode = false;
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
	 * @return \rocket\spec\extr\SpecExtractionManager
	 */
	public function getSpecExtractionManager() {
		if (!$this->specExtractionManager->isInitialized()) {
			$this->specExtractionManager->extract();
		}
		
		return $this->specExtractionManager;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsLaunchPad(string $id) {
		return isset($this->launchPads[$id]);
	}
	
	/**
	 * @param string $id
	 * @return LaunchPad
	 * @throws UnknownLaunchPadException
	 */
	public function getLaunchPadById(string $id) {
		if (isset($this->launchPads[$id])) {
			return $this->launchPads[$id];
		}
		
		throw new UnknownLaunchPadException('Unknown menu item id:  ' . $id);
	}
	
	/**
	 * @return LaunchPad[]
	 */
	public function getLaunchPads() {
		return $this->launchPads;
	}
	
	private function createInvalidLaunchPadConfigurationException($launchPadId, \Exception $previous) {
		throw new InvalidLaunchPadConfigurationException('LaunchPad with following id invalid configured: ' 
				. $launchPadId, 0, $previous);
	}
	
	private function createCustomLaunchPad(TypePath $typePath, CustomType $customType, string $label = null) {
		return $this->launchPads[(string) $typePath] = new CustomLaunchPad($typePath, $customType, $label);
	}
	
	private function createEiLaunchPad(TypePath $typePath, EiMask $eiMask, string $label = null) {
		return $this->launchPads[(string) $typePath] = new EiLaunchPad($typePath, $eiMask, $label);
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
	
	/**
	 * @param N2nContext $n2nContext
	 * @param bool $noSetupMode
	 * @throws InvalidConfigurationException
	 */
	public function initialize(N2nContext $n2nContext, bool $noSetupMode = false) {
		$this->clear();
		
		$this->noSetupMode = $noSetupMode;
		
		$cacheStore = $n2nContext->getAppCache()->lookupCacheStore(Spec::class);
		
		$this->specExtractionManager->load();
		
		$charcs = null;
		if (!N2N::isDevelopmentModeOn() && null !== ($hashCode = $this->specExtractionManager->getModularConfigSource()->hashCode())) {
			$charcs = array('version' => Rocket::VERSION, 'hashCode' => $hashCode);
		}
		
		if ($charcs !== null && null !== ($cacheItem = $cacheStore->get(Spec::class, $charcs))) {
// 			$data = $cacheItem->getData();
			try {
				$this->customTypes = $cacheItem->data['customTypes'] ?? array();
				$this->eiTypes = $cacheItem->data['eiTypes'] ?? array();
				$this->eiTypeCis = $cacheItem->data['eiTypeCis'] ?? array();
				$this->launchPads = $cacheItem->data['launchPads'] ?? array();
				$this->eiTypeSetupQueue->setPropIns($cacheItem->data['propIns'] ?? array());
				$this->eiTypeSetupQueue->setEiConfigurators($cacheItem->data['eiConfigurators'] ?? array());
			} catch (\Throwable $e) {
				$cacheStore->remove(Spec::class, $charcs);
				throw new CorruptedCacheStoreException(null, null, $e);
			}
		} else {
			if (!$this->specExtractionManager->isInitialized()) {
				$this->specExtractionManager->extract();
			}
			
			foreach ($this->specExtractionManager->getCustomTypeExtractions() as $customTypeExtraction) {
				$customType = $this->createCustomTypeFromExtr($customTypeExtraction);
				
				if (null !== ($launchPadExtraction = $customTypeExtraction->getLaunchPadExtraction())) {
					$this->createCustomLaunchPad($launchPadExtraction->getLabel() ?? $customType->getLabel());
				}
			}
			
			foreach ($this->specExtractionManager->getEiTypeExtractions() as $eiTypeExtraction) {
				$eiType = $this->createEiTypeFromExtr($eiTypeExtraction);
				
				if (null !== ($launchPadExtraction = $eiTypeExtraction->getLaunchPadExtraction())) {
					$this->createEiLaunchPad($eiMask, $launchPadExtraction->getLabel() ?? $eiType->getEiMask()->getLabel());
				}
			}
			
// 			foreach ($this->specExtractionManager->getEiModificatorExtractions() as $eiModificatorExtraction) {
// 				$this->buildEiModificatorFromExtr($eiModificatorExtraction);
// 			}
			
// 			foreach ($this->specExtractionManager->getEiTypeExtensionExtractions() as $eiTypeExtensionExtraction) {
// 				$eiTypeExtension = $this->buildEiTypeExtensionFromExtr($eiTypeExtensionExtraction);
				
// 				if ($eiTypeExtension === null) continue;
				
// 				if (null !== ($launchPadExtraction = $eiTypeExtension->getLaunchPadExtraction())) {
// 					$eiMask = $eiType->getEiMask();
// 					$this->createEiLaunchPad($eiMask, $launchPadExtraction->getLabel() ?? $eiMask->getLabel());
// 				}
// 			}
			
			if ($charcs !== null) {
				$cacheStore->store(Spec::class, $charcs, array(
						'customTypes' => $this->customTypes, 'eiTypes' => $this->eiTypes, 
						'eiTypeCis' => $this->eiTypeCis, 'launchPads' => $this->launchPads,
						'propIns' => $this->eiTypeSetupQueue->getPropIns(),
						'eiConfigurators' => $this->eiTypeSetupQueue->getEiConfigurators()));
			}
		}
		
		foreach ($this->eiTypeCis as $className => $eiType) {
			$entityModel = null;
			try {
				$class = ReflectionUtils::createReflectionClass($className);
				$entityModel = $this->entityModelManager->getEntityModelByClass($class);
			} catch (TypeNotFoundException $e) {
// 				if ($this->noSetupMode) continue;
				
				throw new InvalidSpecConfigurationException('Invalid EiType: ' . $eiType, 0, $e);
			} catch (OrmConfigurationException $e) {
// 				if ($this->noSetupMode) continue;
				
				throw new InvalidSpecConfigurationException('Invalid EiType: ' . $eiType, 0, $e);
			}
			
			
			$eiType->setEntityModel($entityModel);
			
			if ($eiType->getEntityModel()->hasSuperEntityModel()) {
				$superClassName = $eiType->getEntityModel()->getSuperEntityModel()->getClass()->getName();
				if (!isset($this->eiTypeCis[$superClassName])) {
					throw new InvalidConfigurationException('EiType required for ' . $superClassName);
				}
				
				$eiType->setSuperEiType($this->eiTypeCis[$superClassName]);
			}
		}
		
		if (!$this->noSetupMode) {
			$this->eiTypeSetupQueue->trigger($n2nContext);
		}
	}
	
	/**
	 * 
	 */
	public function clear() {
		$this->noSetupMode = null;
		$this->customTypes = array();
		$this->eiTypes = array();
		$this->launchPads = array();
	}
	
	private function createEiTypeFromExtr(EiTypeExtraction $eiTypeExtraction) {
		$factory = new EiTypeFactory($this->entityModelManager, $this->getEiTypeSetupQueue());
		
		$typePath = new TypePath($eiTypeExtraction->getId());
		$eiModificationExtractions = $this->specExtractionManager->getEiModificatorExtractionsByEiTypePath($typePath);
		$eiType = $factory->create($eiTypeExtraction, $eiModificationExtractions);
		
		$this->eiTypes[$eiTypeExtraction->getId()] = $eiType;
		$this->eiTypeCis[$eiTypeExtraction->getEntityClassName()] = $eiType;
		
		if ($this->specExtractionManager->containsLaunchPadExtractionTypePath($typePath)) {
			$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByTypePath($typePath);
			$this->launchPads[(string) $launchPadExtraction->getTypePath()] 
					= $this->createEiLaunchPad($typePath, $eiType->getEiMask(),
							$launchPadExtraction->getLabel());
			
		}
		
		foreach ($this->specExtractionManager->getEiTypeExtensionExtractionsByExtendedEiTypePath($typePath)
				as $eiTypeExtensionExtraction) {
			$typePath = new TypePath($eiType->getId(), $eiTypeExtensionExtraction->getId());
			$eiModificatorExtractions = $this->specExtractionManager->getEiModificatorExtractionsByEiTypePath($typePath);
			$eiTypeExtension = $factory->createEiTypeExtension($eiType, $eiTypeExtensionExtraction, $eiModificatorExtractions);
			$eiType->getEiTypeExtensionCollection()->add($eiTypeExtension);
			
			if ($this->specExtractionManager->containsLaunchPadExtractionTypePath($typePath)) {
				$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByTypePath($typePath);
				$this->createEiLaunchPad($typePath, $eiTypeExtension->getEiMask(), $launchPadExtraction->getLabel());
			}
		}
		
		return $eiType;
	}
	
	private function createCustomTypeFromExtr(CustomTypeExtraction $customTypeExtraction) {
		$customType = $this->customTypes[$customTypeExtraction->getId()] = CustomTypeFactory::create($customTypeExtraction);
		$typePath = new TypePath($customTypeExtraction->getId());
		
		if ($this->specExtractionManager->containsLaunchPadExtractionTypePath($typePath)) {
			$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByTypePath($typePath);
			$this->createCustomLaunchPad($typePath, $customType, $launchPadExtraction->getLabel());
		}
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsTypeId(string $id) {
		return isset($this->eiTypes[$id]) || isset($this->customTypes[$id]);
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsCustomTypeId(string $id) {
		return isset($this->customTypes[$id]);
	}
	
	/**
	 *
	 * @param string $id
	 * @return Type
	 * @throws UnknownTypeException
	 * @throws IllegalStateException
	 */
	public function getCustomTypeById(string $id) {
		if (isset($this->customTypes[$id])) {
			return $this->customTypes[$id];
		}
		
		$this->specExtractionManager->getCustomTypeExtractionById($id);
		throw new IllegalStateException('Spec not initialized.');
	}
	
	/**
	 * @return CustomType[]
	 */
	public function getCustomTypes() {
		return $this->customTypes;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsEiTypeId(string $id) {
		return isset($this->eiTypes[$id]);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return bool
	 */
	public function containsEiTypeClass(\ReflectionClass $class) {
		return isset($this->eiTypeCis[$class->getName()]);
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function containsEiTypeClassName(string $className) {
		return isset($this->eiTypeCis[$className]);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\ei\EiType
	 * @throws UnknownTypeException
	 * @throws IllegalStateException
	 */
	public function getEiTypeById(string $id) {
		if (isset($this->eiTypes[$id])) {
			return $this->eiTypes[$id];
		}
		
		$this->specExtractionManager->getEiTypeExtractionById($id);
		throw new IllegalStateException('Spec not initialized.');
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\ei\EiType
	 */
	public function getEiTypeByClass(\ReflectionClass $class) {
		return $this->getEiTypeByClassName($class->getName());
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\ei\EiType
	 */
	public function getEiTypeByClassName(string $className) {
		if (isset($this->eiTypeCis[$className])) {
			return $this->eiTypeCis[$className];
		}
		
		$this->specExtractionManager->getEiTypeExtractionByClassName($className);
		throw new IllegalStateException('Spec not initialized.');
	}
	
	/**
	 * @return EiType[]
	 */
	public function getEiTypes() {
		return $this->eiTypes;
	}
	
	public function getEiTypeSetupQueue() {
		return $this->eiTypeSetupQueue;
	}
}

class EiTypeSetupQueue {
	private $spec;
	private $propIns = array();
	private $eiConfigurators = array();
	private $es = array();
	
	public function __construct(Spec $spec) {
		$this->spec = $spec;
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
			if ($eiConfigurator->getEiComponent()->getEiEngine()->getEiMask()->getEiType()->getId() !== $eiTypeId) {
				continue;
			}
				
			$this->setup($n2nContext, $eiConfigurator);
			unset($this->eiConfigurators[$key]);
		}
	}
	
	private function setup($n2nContext, $eiConfigurator) {
		$eiComponent = $eiConfigurator->getEiComponent();
		$eiSetupProcess = new SpecEiSetupProcess($this->spec, $n2nContext, $eiComponent);
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
