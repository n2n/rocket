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
use rocket\ei\util\model\Eiu;
use rocket\ei\component\EiSetup;

class Spec {	
	private $specExtractionManager;
	private $entityModelManager;
	
	private $launchPads = array();
	private $customTypes = array();
	private $eiTypes = array();
	private $eiTypeCis = array();
	
	private $noSetupMode = false;
	
	/**
	 * @param SpecExtractionManager $specExtractionManager
	 * @param EntityModelManager $entityModelManager
	 */
	public function __construct(SpecExtractionManager $specExtractionManager, EntityModelManager $entityModelManager) {
		$this->specExtractionManager = $specExtractionManager;
		$this->entityModelManager = $entityModelManager;
		$this->eiSetupQueue = new EiSetupQueue($this);
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
	
	/**
	 * @param N2nContext $n2nContext
	 * @param bool $noSetupMode
	 * @throws InvalidConfigurationException
	 */
	public function initialize(N2nContext $n2nContext, bool $noSetupMode = false) {
		$this->clear();
		$this->eiSetupQueue->clear();
		
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
				$this->eiSetupQueue->setPropIns($cacheItem->data['propIns'] ?? array());
				$this->eiSetupQueue->setEiConfigurators($cacheItem->data['eiConfigurators'] ?? array());
			} catch (\Throwable $e) {
				$cacheStore->remove(Spec::class, $charcs);
				throw new CorruptedCacheStoreException(null, null, $e);
			}
		} else {
			if (!$this->specExtractionManager->isInitialized()) {
				$this->specExtractionManager->extract();
			}
			
			foreach ($this->specExtractionManager->getCustomTypeExtractions() as $customTypeExtraction) {
				$this->createCustomTypeFromExtr($customTypeExtraction);
			}
			
			foreach ($this->specExtractionManager->getEiTypeExtractions() as $eiTypeExtraction) {
				 $this->createEiTypeFromExtr($eiTypeExtraction);
			}
			
			if ($charcs !== null) {
				$cacheStore->store(Spec::class, $charcs, array(
						'customTypes' => $this->customTypes, 'eiTypes' => $this->eiTypes, 
						'eiTypeCis' => $this->eiTypeCis, 'launchPads' => $this->launchPads,
						'propIns' => $this->eiSetupQueue->getPropIns(),
						'eiConfigurators' => $this->eiSetupQueue->getEiConfigurators()));
			}
		}
		
		foreach ($this->eiTypeCis as $className => $eiType) {
			$entityModel = null;
			try {
				$class = ReflectionUtils::createReflectionClass($className);
				$entityModel = $this->entityModelManager->getEntityModelByClass($class);
			} catch (TypeNotFoundException $e) {
				throw new InvalidSpecConfigurationException('Invalid EiType: ' . $eiType, 0, $e);
			} catch (OrmConfigurationException $e) {
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
			$this->eiSetupQueue->trigger($n2nContext);
		} else {
			$this->eiSetupQueue->clear();
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
		$factory = new EiTypeFactory($this->entityModelManager, $this->getEiSetupQueue());
		
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
	
	/**
	 * @return \rocket\spec\EiSetupQueue
	 */
	public function getEiSetupQueue() {
		return $this->eiSetupQueue;
	}
}

class EiSetupQueue {
	private $spec;
	private $propIns = array();
	private $eiConfigurators = array();
	
	public function __construct(Spec $spec) {
		$this->spec = $spec;
	}
		
	public function addPropIn(PropIn $propIn) {
		$this->propIns[] = $propIn;
	}
	
	public function addEiConfigurator(EiConfigurator $eiConfigurator) {
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
	
	public function clear()  {
		$this->propIns = array();
		$this->eiConfigurators = array();
	}
		
	public function trigger(N2nContext $n2nContext) {
		$this->propIns();
		
		while (null !== ($eiConfigurator = array_shift($this->eiConfigurators))) {
			$this->setup($n2nContext, $eiConfigurator);
		}
		
		$cbrs = [];
		foreach ($this->spec->getEiTypes() as $eiType) {
			$callbacks = $eiType->getEiMask()->setupEiEngine();
			if (!empty($callbacks)) {
				$cbrs[] = array('em' => $eiType->getEiMask(), 'cb' => $callbacks);	
			}
			
			foreach ($eiType->getEiTypeExtensionCollection() as $eiTypeExtension) {
				$callbacks = $eiTypeExtension->getEiMask()->setupEiEngine();
				if (!empty($callbacks)) {
					$cbrs[] = array('em' => $eiTypeExtension->getEiMask(), 'cb' => $callbacks);
				}
			}
		}
		
		foreach ($cbrs as $cbr) {
			try {
				foreach ($cbr['cb'] as $c) {
					$c($cbr['em']->getEiEngine());
				}
			} catch (InvalidConfigurationException $e) {
				throw new InvalidEiMaskConfigurationException('Failed to setup EiMask '
						. $cbr['cb'] . '.', 0, $e);
			}
		}
	}

// 	public function exclusiveTriggerForEiType($eiTypeId, N2nContext $n2nContext) {
// 		$this->exclusivePropInForEiType($eiTypeId, $n2nContext);
	
// 		foreach ($this->eiConfigurators as $key => $eiConfigurator) {
// 			if ($eiConfigurator->getEiComponent()->getEiEngine()->getEiMask()->getEiType()->getId() !== $eiTypeId) {
// 				continue;
// 			}
				
// 			$this->setup($n2nContext, $eiConfigurator);
// 			unset($this->eiConfigurators[$key]);
// 		}
// 	}
	
	/**
	 * 
	 * @param N2nContext $n2nContext
	 * @param EiConfigurator $eiConfigurator
	 * @throws InvalidEiMaskConfigurationException
	 */
	private function setup($n2nContext, $eiConfigurator) {
		$eiSetup = new EiSetup($this->spec, $n2nContext, 
				$eiConfigurator->getEiComponent());
		
		try {
			try {
				$eiConfigurator->setup($eiSetup);
			} catch (AttributesException $e) {
				throw $eiSetupProcess->createException(null, $e);
			} catch (\InvalidArgumentException $e) {
				throw $eiSetupProcess->createException(null, $e);
			}
		} catch (InvalidConfigurationException $e) {
			throw new InvalidEiMaskConfigurationException('Failed to setup EiMask ' 
					. $eiComponent->getEiMask() . '.', 0, $e);
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
