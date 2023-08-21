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

use rocket\op\ei\component\EiConfigurator;
use n2n\core\container\N2nContext;
use rocket\op\launch\LaunchPad;
use rocket\op\launch\UnknownLaunchPadException;
use n2n\util\type\attrs\AttributesException;
use n2n\reflection\property\PropertiesAnalyzer;
use rocket\op\ei\component\InvalidEiConfigurationException;
use n2n\config\InvalidConfigurationException;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\component\EiSetup;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\op\ei\component\modificator\EiModNature;
use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\spec\result\EiErrorResult;
use rocket\op\spec\result\EiPropError;
use rocket\op\spec\result\EiModificatorError;
use rocket\op\spec\result\EiCommandError;
use rocket\op\ei\EiType;
use rocket\op\ei\UnknownEiTypeException;
use rocket\op\launch\MenuGroup;
use rocket\op\spec\setup\EiTypeFactory;
use n2n\util\ex\DuplicateElementException;
use ReflectionClass;
use n2n\persistence\orm\model\UnknownEntityPropertyException;

class Spec {
	/**
	 * @var EiTypeFactory
	 */
	private EiTypeFactory $eiTypeFactory;

	private array $eiTypes = array();
	private array $ciEiTypes = array();
	private array $menuGroups = [];
	private array $launchPads = [];

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

		throw new UnknownLaunchPadException('Unknown LaunchPad id: ' . $id);
	}

	function addLaunchPad(LaunchPad $launchPad) {
		$id = $launchPad->getId();

		if (isset($this->launchPads[$id])) {
			throw new DuplicateElementException('Duplicated LaunchPad id: ' . $id);
		}

		$this->launchPads[$id] = $launchPad;
	}

	/**
	 * @return MenuGroup[]
	 */
	function getMenuGroups(): array {
		uasort($this->menuGroups, function(MenuGroup $first, MenuGroup $second){
			return $first->getOrderIndex() < $second->getOrderIndex() ? -1 : 1;
		});

		return $this->menuGroups;
	}

	function putMenuGroup(string $groupKey, MenuGroup $menuGroup) {
		$this->menuGroups[$groupKey] = $menuGroup;
	}

	function containsMenuGroupKey(string $groupkey) {
		return isset($this->menuGroups[$groupkey]);
	}

	function getMenuGroup(string $groupKey): MenuGroup {
		if (isset($this->menuGroups[$groupKey])) {
			return $this->menuGroups[$groupKey];
		}

		throw new UnknownMenuGroupException();
	}

	function addEiType(EiType $eiType) {
		$id = $eiType->getId();

		if (isset($this->eiTypes[$id])) {
			throw new DuplicateElementException('Duplicated EiType id: ' . $id);
		}

		$this->eiTypes[$eiType->getId()] = $eiType;
		$this->ciEiTypes[$eiType->getClass()->getName()] = $eiType;
	}
	
// 	/**
// 	 * @return LaunchPad[]
// 	 */
// 	public function getLaunchPads() {
// 		return $this->launchPads;
// 	}
	
// 	private function createInvalidLaunchPadConfigurationException($launchPadId, \Exception $previous) {
// 		throw new InvalidLaunchPadConfigurationException('LaunchPad with following id invalid configured: ' 
// 				. $launchPadId, 0, $previous);
// 	}
	
// 	private function createCustomLaunchPad(TypePath $typePath, CustomType $customType, string $label = null) {
// 		return $this->launchPads[(string) $typePath] = new CustomLaunchPad($typePath, $customType, $label);
// 	}
	
// 	private function createEiLaunchPad(TypePath $typePath, EiMask $eiMask, string $label = null) {
// 		return $this->launchPads[(string) $typePath] = new EiLaunchPad($typePath, $eiMask, $label);
// 	}
	
	/**
	 * 
	 */
	public function clear(): void {
		$this->eiTypes = array();
		$this->ciEiTypes = [];
		$this->menuGroups = array();
		$this->launchPads = array();
	}
//
//	private function isEiTypeAnnotated(\ReflectionClass $class) {
//		return ReflectionContext::getAttributeSet($class)->hasClassAttribute(\rocket\attribute\EiType::class);
//	}



	
//	private function assembleEiTypeExtensions(EiMask $extendedEiMask, TypePath $extenedTypePath) {
//		$eiType = $extendedEiMask->getEiType();
//
//		foreach ($this->specExtractionManager->getEiTypeExtensionExtractionsByExtendedEiTypePath($extenedTypePath)
//				as $eiTypeExtensionExtraction) {
//			$typePath = new TypePath($eiType->getId(), $eiTypeExtensionExtraction->getId());
//			$eiModificatorExtractions = $this->specExtractionManager->getEiModificatorExtractionsByEiTypePath($typePath);
//			$eiTypeExtension = $this->eiTypeFactory->createEiTypeExtension($extendedEiMask, $eiTypeExtensionExtraction, $eiModificatorExtractions);
//			$eiType->getEiTypeExtensionCollection()->add($eiTypeExtension);
//
//			$this->eiSetupQueue->addEiMask($eiTypeExtension->getEiMask());
//			$this->assembleEiTypeExtensions($eiTypeExtension->getEiMask(), $typePath);
//		}
//	}

	
//	private function initLaunchPadFromTypePath(TypePath $typePath) {
//		$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByTypePath($typePath);
//
//		if ($typePath->getEiTypeExtensionId() === null && !$this->specExtractionManager->containsEiTypeId($typePath->getTypeId())) {
//			return $this->launchPads[(string) $typePath] = new CustomLaunchPad($typePath, $this->getCustomTypeById($typePath->getTypeId()));
//		}
//
//		$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByEiTypePath($typePath);
//
//		$eiType = $this->getEiTypeById($typePath->getTypeId());
//		$eiMask = null;
//		if ($typePath->getEiTypeExtensionId() !== null) {
//			$eiMask = $eiType->getEiTypeExtensionCollection()->getById($typePath->getEiTypeExtensionId())->getEiMask();
//		} else {
//			$eiMask = $eiType->getEiMask();
//		}
//
//		return $this->launchPads[(string) $typePath] = new EiLaunchPad($typePath, $eiMask, $launchPadExtraction->getLabel());
//	}
//
//	/**
//	 * @param string $id
//	 * @return boolean
//	 */
//	public function containsTypeId(string $id) {
//		return isset($this->eiTypes[$id]) || isset($this->customTypes[$id]) || $this->specExtractionManager->containsTypeId($id);
//	}
//
//	/**
//	 * @param string $id
//	 * @return bool
//	 */
//	public function containsCustomTypeId(string $id) {
//		return isset($this->customTypes[$id]);
//	}
//
//	/**
//	 *
//	 * @param string $id
//	 * @return Type
//	 * @throws UnknownTypeException
//	 * @throws IllegalStateException
//	 */
//	public function getCustomTypeById(string $id) {
//		if (isset($this->customTypes[$id])) {
//			return $this->customTypes[$id];
//		}
//
//		return $this->initCustomTypeFromExtr($this->specExtractionManager->getCustomTypeExtractionById($id));
//	}
//
//	/**
//	 * @return CustomType[]
//	 */
//	public function getCustomTypes() {
//		return $this->customTypes;
//	}
//
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsEiTypeId(string $id): bool {
		return isset($this->eiTypes[$id]) || $this->specExtractionManager->containsCustomTypeId($id);
	}
	
	/**
	 * @param ReflectionClass $class
	 * @return bool
	 */
	public function containsEiTypeClass(ReflectionClass $class): bool {
		return $this->containsEiTypeClassName($class->getName());
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function containsEiTypeClassName(string $className): bool {
		return isset($this->ciEiTypes[$className]);
	}

	/**
	 * @param string $id
	 * @return EiType
	 */
	public function getEiTypeById(string $id): EiType {
		if (isset($this->eiTypes[$id])) {
			return $this->eiTypes[$id];
		}
		
		throw new UnknownEiTypeException('EiType with id \''. $id . '\' not found.');
	}
	
	/**
	 * @param ReflectionClass $class
	 * @return EiType
	 * @throws UnknownEiTypeException
	 */
	public function getEiTypeByClass(ReflectionClass $class) {
		return $this->getEiTypeByClassName($class->getName());
	}
	
	/**
	 * @param string $className
	 * @return EiType
	 * @throws UnknownEiTypeException
	 */
	public function getEiTypeByClassName(string $className) {
		if (isset($this->ciEiTypes[$className])) {
			return $this->ciEiTypes[$className];
		}

		throw new UnknownEiTypeException('EiType for class \'' . $className . '\' not found.');
	}
	
	/**
	 * @param object $entityObj
	 * @return EiType
	 * @throws UnknownEiTypeException
	 */
	public function getEiTypeOfObject(object $entityObj) {
		$class = new ReflectionClass($entityObj);
		$orgClassName = $class->getName();
		
		do {
			$className = $class->getName();
			if ($this->containsEiTypeClassName($className)) {
				return $this->getEiTypeByClassName($className);
			}
		} while ($class = $class->getParentClass());
		
		throw new UnknownEiTypeException('No EiType found for class: ' . get_class($entityObj));
	}

	
	/**
	 * @return EiType[]
	 */
	public function getEiTypes() {
		return $this->eiTypes;
	}
}

class EiSetupQueue {
	private $eiErrorResult;
	private $n2nContext;
	
	private $propIns = array();
	private $eiMasks = array();
	private $eiPropSetupTasks = array();
	private $eiModificatorSetupTasks = array();
	private $eiCmdSetupTasks = array();
	
	public function __construct(?EiErrorResult $eiErrorResult, N2nContext $n2nContext) {
		$this->eiErrorResult = $eiErrorResult;
		$this->n2nContext = $n2nContext;
	}
		
//	public function addPropIn(PropIn $propIn) {
//		$this->propIns[] = $propIn;
//	}
	
	public function addEiMask(EiMask $eiMask) {
		$this->eiMasks[] = $eiMask;
	}
	
	function getEiMasks() {
		return $this->eiMasks;
	}
	
	public function addEiPropConfigurator(EiPropNature $eiProp, EiConfigurator $eiConfigurator) {
		$this->eiPropSetupTasks[] = new EiPropSetupTask($eiProp, $eiConfigurator);
	}
	
	public function addEiModificatorConfigurator(EiModNature $eiModificator, EiConfigurator $eiConfigurator) {
		$this->eiModificatorSetupTasks[] = new EiModificatorSetupTask($eiModificator, $eiConfigurator);
	}
	
	public function addEiCommandConfigurator(EiCmdNature $eiCmd, EiConfigurator $eiConfigurator) {
		$this->eiCmdSetupTasks[] = new EiCommandSetupTask($eiCmd, $eiConfigurator);
	}
	
	public function getPropIns() {
		return $this->propIns;
	}
	
	public function setPropIns(array $propIns) {
		$this->propIns = $propIns;
	}
	
// 	public function getEiConfigurators() {
// 		return $this->eiConfigurators;
// 	}
	
// 	public function setEiConfigurators(array $eiConfigurators) {
// 		$this->eiConfigurators = $eiConfigurators;
// 	}
	
	public function clear()  {
		$this->propIns = array();
		//$this->eiConfigurators = array();
		$this->eiPropSetupTasks = array();
		$this->eiModificatorSetupTasks = array();
		$this->eiCmdSetupTasks = array();
	}
		
	public function trigger() {
		$this->propIns();
				
// 		while (null !== ($eiConfigurator = array_shift($this->eiConfigurators))) {
// 			$this->setup($n2nContext, $eiConfigurator);
// 		}
		$eiMaskCallbackProcess = new EiMaskCallbackProcess($this);
		
		while (null !== ($eiPropSetupTask = array_shift($this->eiPropSetupTasks))) {
			try {
				$this->setup($eiPropSetupTask->getEiConfigurator());
				$eiMaskCallbackProcess->check($eiPropSetupTask->getEiProp());
			} catch (\Throwable $t) {
				if ($this->eiErrorResult === null) {
					throw $t;
				}
				
				$this->eiErrorResult->putEiPropError(EiPropError::fromEiProp($eiPropSetupTask->getEiProp(), $t));
			}
		}
		
		while (null !== ($eiModificatorSetupTask = array_shift($this->eiModificatorSetupTasks))) {
			try {
				$this->setup($eiModificatorSetupTask->getEiConfigurator());
				$eiMaskCallbackProcess->check(null, $eiModificatorSetupTask->getEiModificator());
			} catch (\Throwable $t) {
				if ($this->eiErrorResult === null) {
					throw $t;
				}
				
				$this->eiErrorResult->putEiModificatorError(
						EiModificatorError::fromEiModificator($eiModificatorSetupTask->getEiModificator(), $t));
			}
		}
		
 		while (null !== ($eiCmdSetupTask = array_shift($this->eiCmdSetupTasks))) {
			try {
				$this->setup($eiCmdSetupTask->getEiConfigurator());
				$eiMaskCallbackProcess->check(null, null, $eiCmdSetupTask->getEiCommand());
			} catch (\Throwable $e) {
				if ($this->eiErrorResult === null) {
					throw $e;
				}
				
				$this->eiErrorResult->putEiCommandError(
						EiCommandError::fromEiCommand($eiCmdSetupTask->getEiCommand(), $e));
			}
		}
		
		while (null !== ($eiMask = array_shift($this->eiMasks))) {
			$eiMask->setupEiEngine();
		}
		
		$eiMaskCallbackProcess->run($this->eiErrorResult);
		
// 		foreach ($cbrs as $cbr) {
// 			try {
// 				foreach ($cbr['cb'] as $c) {
// 					$c($cbr['em']->getEiEngine());
// 				}
// 			} catch (InvalidConfigurationException $e) {
// 				throw new InvalidEiMaskConfigurationException('Failed to setup EiMask.', 0, $e);
// 			}
// 		}
		
		return $this->eiErrorResult;
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
	 * @param EiConfigurator $eiConfigurator
	 * @throws InvalidEiMaskConfigurationException
	 */
	private function setup($eiConfigurator) {
		$eiSetup = new EiSetup($this->n2nContext, $eiConfigurator->getEiComponent());
		
		try {
			try {
				$eiConfigurator->setup($eiSetup);
			} catch (AttributesException $e) {
				throw $eiSetup->createException(null, $e);
			} catch (\InvalidArgumentException $e) {
				throw $eiSetup->createException(null, $e);
			}
		} catch (InvalidConfigurationException $e) {
			throw new InvalidEiMaskConfigurationException('Failed to setup EiMask for: ' 
					. $eiConfigurator->getEiComponent() . '.', 0, $e);
		}
	}
	
	public function propIns() {
		while (null !== ($propIns = array_shift($this->propIns))) {
			$propIns->invoke($this->eiErrorResult);
		}
	}
	
	public function exclusivePropInForEiType($eiTypeId) {
		foreach ($this->propIns as $key => $propIn) {
			if ($propIn->getEiType()->getId() !== $eiTypeId) {
				continue;
			}
		
			$propIn->invoke($this->eiErrorResult);
			unset($this->propIns[$key]);
		}
	}
}

class EiMaskCallbackProcess {
	private $spec;
	private $callbackConfigurations = [];
	
	function __construct(EiSetupQueue $spec) {
		$this->spec = $spec;
	}
	
	function check(EiPropNature $eiProp = null, EiModNature $eiModificator = null, EiCmdNature $eiCmd = null) {
		foreach ($this->spec->getEiMasks() as $eiMask) {
			$this->checkCallbacks($eiMask, $eiProp, $eiModificator, $eiCmd);
		}
	}
	
	function run(EiErrorResult $eiErrorResult = null) {
		foreach ($this->callbackConfigurations as $callbackConfiguration) {
			try {
				try {
					$callbackConfiguration['callback']($callbackConfiguration['eiMask']->getEiEngine());
				} catch (InvalidConfigurationException $e) {
						throw new InvalidEiMaskConfigurationException('Failed to setup EiMask.', 0, $e);
				}
			} catch (\Throwable $t) {
				if (null === $eiErrorResult) {
					throw $t;
				}
				
				if (null !== $callbackConfiguration['eiProp']) {
					$eiErrorResult->putEiPropError(EiPropError::fromEiProp($callbackConfiguration['eiProp'], $t));
				}
				
				if (null !== $callbackConfiguration['eiModificator']) {
					$eiErrorResult->putEiModificatorError(EiModificatorError::fromEiModificator($callbackConfiguration['eiModificator'], $t));
				}
				
				if (null !== $callbackConfiguration['eiCmd']) {
					$eiErrorResult->putEiCommandError(EiCommandError::fromEiCommand($callbackConfiguration['eiCmd'], $t));
				}
			}
		}
	}
	
	private function checkCallbacks(EiMask $eiMask, EiPropNature $eiProp = null,
			EiModNature $eiModificator = null, EiCmdNature $eiCmd = null) {
		//$newCallbacks = [];
		foreach ($eiMask->getEiEngineSetupCallbacks() as $objHash => $callback) {
			if (isset($this->callbackConfigurations[$objHash])) {
				continue;
			}
			
			//$newCallbacks[] = $callback;
			$this->callbackConfigurations[$objHash] = ['callback' => $callback, 'eiMask' => $eiMask, 
					'eiProp' => $eiProp, 'eiModificator' => $eiModificator, 'eiCmd' => $eiCmd];
		}
		
		// return $newCallbacks;
	}
}
//
//class PropIn {
//	private $eiType;
//	private $eiPropConfigurator;
//	private $objectPropertyName;
//	private $entityPropertyName;
//	private $contextEntityPropertyNames;
//
//	public function __construct(EiType $eiType, $eiPropConfigurator, $objectPropertyName, $entityPropertyName, array $contextEntityPropertyNames) {
//		$this->eiType = $eiType;
//		$this->eiPropConfigurator = $eiPropConfigurator;
//		$this->objectPropertyName = $objectPropertyName;
//		$this->entityPropertyName = $entityPropertyName;
//		$this->contextEntityPropertyNames = $contextEntityPropertyNames;
//	}
//
//	public function getEiType() {
//		return $this->eiType;
//	}
//
//	public function invoke(EiErrorResult $eiErrorResult = null) {
//		$entityPropertyCollection = $this->eiType->getEntityModel();
//		$class = $entityPropertyCollection->getClass();
//
//		$contextEntityPropertyNames = $this->contextEntityPropertyNames;
//		while (null !== ($cepn = array_shift($contextEntityPropertyNames))) {
//			$entityPropertyCollection = $entityPropertyCollection->getEntityPropertyByName($cepn)
//					->getEmbeddedEntityPropertyCollection();
//			$class = $entityPropertyCollection->getClass();
//		}
//
//		$accessProxy = null;
//		if (null !== $this->objectPropertyName) {
//			try{
//				$propertiesAnalyzer = new PropertiesAnalyzer($class, false);
//				$accessProxy = $propertiesAnalyzer->analyzeProperty($this->objectPropertyName, false, true);
//				$accessProxy->setNullReturnAllowed(true);
//			} catch (\ReflectionException $e) {
//				$this->handleException(new InvalidEiConfigurationException('EiProp is assigned to unknown property: '
//						. $this->objectPropertyName, 0, $e), $eiErrorResult);
//			}
//		}
//
//		$entityProperty = null;
//		if (null !== $this->entityPropertyName) {
//			try {
//				$entityProperty = $entityPropertyCollection->getEntityPropertyByName($this->entityPropertyName, true);
//			} catch (UnknownEntityPropertyException $e) {
//				$this->handleException(new InvalidEiConfigurationException('EiProp is assigned to unknown EntityProperty: '
//						. $this->entityPropertyName, 0, $e), $eiErrorResult);
//			}
//		}
//
//// 		if ($entityProperty !== null || $accessProxy !== null) {
//			try {
//				$this->eiPropConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
//			} catch (IncompatiblePropertyException $e) {
//				$this->handleException($e, $eiErrorResult);
//			}
//// 		}
//	}
//
//	private function handleException($e, EiErrorResult $eiErrorResult = null) {
//		$e = $this->createException($e);
//		if (null !== $eiErrorResult) {
//			$eiErrorResult->putEiPropError(EiPropError::fromEiProp($this->eiPropConfigurator->getEiComponent(), $e));
//			return;
//		}
//
//		throw $e;
//	}
//
//	/**
//	 * @param \Throwable $e
//	 * @return \rocket\op\ei\component\InvalidEiConfigurationException
//	 */
//	private function createException($e) {
//		$eiComponent = $this->eiPropConfigurator->getEiComponent();
//
//		return new InvalidEiConfigurationException('EiProp is invalid configured: ' . $eiComponent . ' in '
//				. $eiComponent->getWrapper()->getEiPropCollection()->getEiMask(), 0, $e);
//	}
//}
//class PropIn {
//	private $eiType;
//	private $eiPropConfigurator;
//	private $objectPropertyName;
//	private $entityPropertyName;
//	private $contextEntityPropertyNames;
//
//	public function __construct(EiType $eiType, $eiPropConfigurator, $objectPropertyName, $entityPropertyName, array $contextEntityPropertyNames) {
//		$this->eiType = $eiType;
//		$this->eiPropConfigurator = $eiPropConfigurator;
//		$this->objectPropertyName = $objectPropertyName;
//		$this->entityPropertyName = $entityPropertyName;
//		$this->contextEntityPropertyNames = $contextEntityPropertyNames;
//	}
//
//	public function getEiType() {
//		return $this->eiType;
//	}
//
//	public function invoke(EiErrorResult $eiErrorResult = null) {
//		$entityPropertyCollection = $this->eiType->getEntityModel();
//		$class = $entityPropertyCollection->getClass();
//
//		$contextEntityPropertyNames = $this->contextEntityPropertyNames;
//		while (null !== ($cepn = array_shift($contextEntityPropertyNames))) {
//			$entityPropertyCollection = $entityPropertyCollection->getEntityPropertyByName($cepn)
//					->getEmbeddedEntityPropertyCollection();
//			$class = $entityPropertyCollection->getClass();
//		}
//
//		$accessProxy = null;
//		if (null !== $this->objectPropertyName) {
//			try{
//				$propertiesAnalyzer = new PropertiesAnalyzer($class, false);
//				$accessProxy = $propertiesAnalyzer->analyzeProperty($this->objectPropertyName, false, true);
//				$accessProxy->setNullReturnAllowed(true);
//			} catch (\ReflectionException $e) {
//				$this->handleException(new InvalidEiConfigurationException('EiProp is assigned to unknown property: '
//						. $this->objectPropertyName, 0, $e), $eiErrorResult);
//			}
//		}
//
//		$entityProperty = null;
//		if (null !== $this->entityPropertyName) {
//			try {
//				$entityProperty = $entityPropertyCollection->getEntityPropertyByName($this->entityPropertyName, true);
//			} catch (UnknownEntityPropertyException $e) {
//				$this->handleException(new InvalidEiConfigurationException('EiProp is assigned to unknown EntityProperty: '
//						. $this->entityPropertyName, 0, $e), $eiErrorResult);
//			}
//		}
//
//// 		if ($entityProperty !== null || $accessProxy !== null) {
//			try {
//				$this->eiPropConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
//			} catch (IncompatiblePropertyException $e) {
//				$this->handleException($e, $eiErrorResult);
//			}
//// 		}
//	}
//
//	private function handleException($e, EiErrorResult $eiErrorResult = null) {
//		$e = $this->createException($e);
//		if (null !== $eiErrorResult) {
//			$eiErrorResult->putEiPropError(EiPropError::fromEiProp($this->eiPropConfigurator->getEiComponent(), $e));
//			return;
//		}
//
//		throw $e;
//	}
//
//	/**
//	 * @param \Throwable $e
//	 * @return \rocket\op\ei\component\InvalidEiConfigurationException
//	 */
//	private function createException($e) {
//		$eiComponent = $this->eiPropConfigurator->getEiComponent();
//
//		return new InvalidEiConfigurationException('EiProp is invalid configured: ' . $eiComponent . ' in '
//				. $eiComponent->getWrapper()->getEiPropCollection()->getEiMask(), 0, $e);
//	}
//}

class EiCommandSetupTask {
	private $eiCmd;
	private $eiConfigurator;
	
	public function __construct(EiCmdNature $eiCmd, EiConfigurator $eiConfigurator) {
		$this->eiCmd = $eiCmd;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\op\ei\component\command\EiCmdNature
	 */
	public function getEiCommand() {
		return $this->eiCmd;
	}
	
	/**
	 * @return \rocket\op\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}

class EiModificatorSetupTask {
	private $eiModificator;
	private $eiConfigurator;
	
	public function __construct(EiModNature $eiModificator, EiConfigurator $eiConfigurator) {
		$this->eiModificator = $eiModificator;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\op\ei\component\modificator\EiModNature
	 */
	public function getEiModificator() {
		return $this->eiModificator;
	}
	
	/**
	 * @return \rocket\op\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}

class EiPropSetupTask {
	private $eiProp;
	private $eiConfigurator;
	
	public function __construct(EiPropNature $eiProp, EiConfigurator $eiConfigurator) {
		$this->eiProp = $eiProp;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\op\ei\component\prop\EiPropNature
	 */
	public function getEiProp() {
		return $this->eiProp;
	}
	
	/**
	 * @return \rocket\op\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}
