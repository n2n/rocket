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

use n2n\persistence\orm\model\EntityModelManager;
use rocket\ei\component\EiConfigurator;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\core\model\launch\LaunchPad;
use rocket\core\model\launch\UnknownLaunchPadException;
use n2n\util\type\attrs\AttributesException;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use rocket\ei\component\InvalidEiConfigurationException;
use n2n\persistence\orm\model\UnknownEntityPropertyException;
use n2n\config\InvalidConfigurationException;
use rocket\ei\mask\EiMask;
use rocket\ei\component\EiSetup;
use n2n\util\type\ArgUtils;
use rocket\ei\component\prop\EiPropNature;
use rocket\ei\component\modificator\EiModNature;
use rocket\ei\component\command\EiCmdNature;
use rocket\spec\result\EiErrorResult;
use rocket\spec\result\EiPropError;
use rocket\spec\result\EiModificatorError;
use rocket\spec\result\EiCommandError;
use rocket\ei\EiType;
use rocket\ei\UnknownEiTypeException;
use rocket\ei\EiLaunchPad;
use n2n\reflection\ReflectionContext;
use rocket\attribute\MenuItem;
use rocket\core\model\launch\MenuGroup;
use n2n\util\StringUtils;
use rocket\spec\setup\EiTypeFactory;
use rocket\spec\setup\SpecConfigLoader;
use n2n\reflection\ReflectionUtils;
use rocket\attribute\EiNestedSet;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\util\ex\err\ConfigurationError;
use rocket\attribute\EiDisplayScheme;

class Spec {
	/**
	 * @var EiTypeFactory
	 */
	private EiTypeFactory $eiTypeFactory;

	private array $eiTypes = array();
	private array $menuGroups = [];
	private array $launchPads = [];

	/**
	 * @param SpecConfigLoader $specConfigLoader
	 * @param EntityModelManager $entityModelManager
	 */
	public function __construct(SpecConfigLoader $specConfigLoader, EntityModelManager $entityModelManager) {
		$this->eiTypeFactory = new EiTypeFactory($specConfigLoader, $entityModelManager);
	}

	public function reload(): void {
		$this->eiTypes = [];

		foreach ($this->eiTypeFactory->getEntityModelManager()->getEntityClasses() as $entityClass) {
			$this->initEiTypeFromClass($entityClass, false);
		}
	}


	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsLaunchPad(string $id) {
		return isset($this->launchPads[$id]) || $this->specExtractionManager->containsLaunchPadExtractionTypePath(TypePath::create($id));
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

		return $this->initLaunchPadFromTypePath(TypePath::create($id));
	}

	/**
	 * @return MenuGroup[]
	 */
	function getMenuGroups() {
		return $this->menuGroups;
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
		$this->menuGroups = array();
		$this->launchPads = array();
	}

	private function isEiTypeAnnotated(\ReflectionClass $class) {
		return ReflectionContext::getAttributeSet($class)->hasClassAttribute(\rocket\attribute\EiType::class);
	}

	private function classNameToId(string $className) {
		return str_replace('\\', '-', $className);
	}

	private function idToClassName(string $id) {
		return str_replace('-', '\\', $id);
	}


	private function initEiTypeFromClassName(string $className, bool $required) {
		if (isset($this->eiTypes[$className])) {
			return $this->eiTypes[$className];
		}

		return $this->initEiTypeFromClass(new \ReflectionClass($className));
	}

	/**
	 * @param \ReflectionClass $class
	 * @param bool $required
	 * @return EiType|null
	 * @throws UnknownEiTypeException
	 */
	private function initEiTypeFromClass(\ReflectionClass $class, bool $required) {
		$className = $class->getName();

		if (isset($this->eiTypes[$className])) {
			return $this->eiTypes[$className];
		}

		$attributeSet = ReflectionContext::getAttributeSet($class);
		$eiTypeAttribute = $attributeSet->getClassAttribute(\rocket\attribute\EiType::class);
		if ($eiTypeAttribute === null) {
			if (!$required) {
				return null;
			}

			throw new UnknownEiTypeException($className . ' is not annotated with attribute '
					. \rocket\attribute\EiType::class);
		}

		$eiTypeA = $eiTypeAttribute->getInstance();
		$label = $eiTypeA->label ?? StringUtils::pretty($class->getShortName());
		$pluralLabel = $eiTypeA->pluralLabel ?? $label;

		$this->eiTypes[$className] = $eiType = $this->eiTypeFactory->create($this->classNameToId($className), $class,
				$label, $pluralLabel);
		if ($eiTypeA->icon !== null) {
			$eiType->getEiMask()->getDef()->setIconType($eiTypeA->icon);
		}

		$this->checkForNestedSet($eiType);
		$this->checkForDisplayScheme($eiType);

		if ($eiType->getEntityModel()->hasSuperEntityModel()) {
			$superClass = $eiType->getEntityModel()->getSuperEntityModel()->getClass();
			
			try {
				$eiType->setSuperEiType($this->initEiTypeFromClass($superClass, true));
			} catch (UnknownEiTypeException $e) {
				throw new InvalidEiConfigurationException('EiType for ' . $class->getName()
						. ' requires super EiType for ' . $superClass->getName(), 0, $e);
			}
		}
		
//		$this->eiSetupQueue->addEiMask($eiType->getEiMask());
//		$this->assembleEiTypeExtensions($eiType->getEiMask(), $typePath);
		
		foreach ($eiType->getEntityModel()->getSubEntityModels() as $subEntityModel) {
			$class = $subEntityModel->getClass();
			
			if ($this->containsEiTypeClass($class)) {
				$this->initEiTypeFromClass($class, false);
			}
		}

		$this->eiTypeFactory->assemble($eiType);

		$this->checkForMenuItem($eiType);
		
		return $eiType;
	}

	private function checkForNestedSet(EiType $eiType) {
		$nestedSetAttribute = ReflectionContext::getAttributeSet($eiType->getEntityModel()->getClass())
				->getClassAttribute(EiNestedSet::class);
		if ($nestedSetAttribute === null) {
			return;
		}

		$nestedSet = $nestedSetAttribute->getInstance();
		try {
			$eiType->setNestedSetStrategy(new NestedSetStrategy(CrIt::p($nestedSet->leftProp),
					CrIt::p($nestedSet->rightProp)));
		} catch (\InvalidArgumentException $e) {
			throw new ConfigurationError($e->getMessage(), $nestedSetAttribute->getFile(),
					$nestedSetAttribute->getLine(), previous: $e);
		}
	}

	private function checkForDisplayScheme(EiType $eiType) {
		$displaySchemeAttribute = ReflectionContext::getAttributeSet($eiType->getEntityModel()->getClass())
				->getClassAttribute(EiDisplayScheme::class);
		if ($displaySchemeAttribute === null) {
			return;
		}

		$displaySchemeA = $displaySchemeAttribute->getInstance();
		$displayScheme = $eiType->getEiMask()->getDisplayScheme();

		$displayScheme->setOverviewDisplayStructure($displaySchemeA->compactDisplayStructure);
		$displayScheme->setBulkyDisplayStructure($displaySchemeA->bulkyDisplayStructure);
		$displayScheme->setDetailDisplayStructure($displaySchemeA->bulkyDetailDisplayStructure);
		$displayScheme->setEditDisplayStructure($displaySchemeA->bulkyEditDisplayStructure);
		$displayScheme->setAddDisplayStructure($displaySchemeA->bulkyDetailDisplayStructure);

	}

	private function checkForMenuItem(EiType $eiType) {
		$menuItemAttribute = ReflectionContext::getAttributeSet($eiType->getEntityModel()->getClass())
				->getClassAttribute(MenuItem::class);

		if ($menuItemAttribute === null) {
			return;
		}

		/**
		 * @var MenuItem $menuItem
		 */
		$menuItem = $menuItemAttribute->getInstance();
		$launchPad = new EiLaunchPad($eiType->getId(), $eiType->getEiMask(), $menuItem->name);

		$groupKey = $menuItem->groupKey;
		$groupName = $menuItem->groupName;

		if ($groupKey !== null) {
			$menuGroup = $this->menuGroups[$groupKey]
					?? $this->menuGroups[$groupKey] = new MenuGroup($groupName ?? StringUtils::pretty($groupKey));

			if ($groupName !== null) {
				$menuGroup->setLabel($groupName);
			}

			$menuGroup->addLaunchPad($launchPad);
			$this->launchPads[$launchPad->getId()] = $launchPad;
			return;
		}

		if ($groupName === null) {
			$groupName = 'Content';
		}

		$menuGroup = $this->menuGroups[$groupName] ?? $this->menuGroups[$groupName] = new MenuGroup($groupName);
		$menuGroup->addLaunchPad($launchPad);
		$this->launchPads[$launchPad->getId()] = $launchPad;
	}

	
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
	public function containsEiTypeId(string $id) {
		return isset($this->eiTypes[$id]) || $this->specExtractionManager->containsCustomTypeId($id);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return bool
	 */
	public function containsEiTypeClass(\ReflectionClass $class) {
		return $this->containsEiTypeClassName($class->getName());
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function containsEiTypeClassName(string $className) {
		return isset($this->eiTypes[$className]);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return EiType
	 * @throws UnknownTypeException
	 * @throws IllegalStateException
	 */
	public function getEiTypeById(string $id) {
		if (isset($this->eiTypes[$id])) {
			return $this->eiTypes[$id];
		}
		
		$eiType = $this->initEiTypeFromExtr($this->specExtractionManager->getEiTypeExtractionById($id));
		$this->triggerEiSetup();
		return $eiType;
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return EiType
	 */
	public function getEiTypeByClass(\ReflectionClass $class) {
		return $this->getEiTypeByClassName($class->getName());
	}
	
	/**
	 * @param string $className
	 * @return EiType
	 * @throws UnknownEiTypeException
	 */
	public function getEiTypeByClassName(string $className) {
		if (isset($this->eiTypes[$className])) {
			return $this->eiTypes[$className];
		}
		
		return $this->initEiTypeFromClass(ReflectionUtils::createReflectionClass($className), true);
	}
	
	/**
	 * @param object $entityObj
	 * @return EiType
	 * @throws UnknownEiTypeException
	 */
	public function getEiTypeOfObject(object $entityObj) {
		$class = new \ReflectionClass($entityObj);
		$orgClassName = $class->getName();
		
		do {
			$className = $class->getName();
			if ($this->containsEiTypeClassName($className)) {
				return $this->initEiTypeFromClassName($className, true);
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
	
	/**
	 * @return \rocket\spec\EiSetupQueue
	 */
	public function getEiSetupQueue() {
		return $this->eiSetupQueue;
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
		
	public function addPropIn(PropIn $propIn) {
		$this->propIns[] = $propIn;
	}
	
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

class PropIn {
	private $eiType;
	private $eiPropConfigurator;
	private $objectPropertyName;
	private $entityPropertyName;
	private $contextEntityPropertyNames;

	public function __construct(EiType $eiType, $eiPropConfigurator, $objectPropertyName, $entityPropertyName, array $contextEntityPropertyNames) {
		$this->eiType = $eiType;
		$this->eiPropConfigurator = $eiPropConfigurator;
		$this->objectPropertyName = $objectPropertyName;
		$this->entityPropertyName = $entityPropertyName;
		$this->contextEntityPropertyNames = $contextEntityPropertyNames;
	}

	public function getEiType() {
		return $this->eiType;
	}
	
	public function invoke(EiErrorResult $eiErrorResult = null) {
		$entityPropertyCollection = $this->eiType->getEntityModel();
		$class = $entityPropertyCollection->getClass();
		
		$contextEntityPropertyNames = $this->contextEntityPropertyNames;
		while (null !== ($cepn = array_shift($contextEntityPropertyNames))) {
			$entityPropertyCollection = $entityPropertyCollection->getEntityPropertyByName($cepn)
					->getEmbeddedEntityPropertyCollection();
			$class = $entityPropertyCollection->getClass();
		}
		
		$accessProxy = null;
		if (null !== $this->objectPropertyName) {
			try{
				$propertiesAnalyzer = new PropertiesAnalyzer($class, false);
				$accessProxy = $propertiesAnalyzer->analyzeProperty($this->objectPropertyName, false, true);
				$accessProxy->setNullReturnAllowed(true);
			} catch (ReflectionException $e) {
				$this->handleException(new InvalidEiConfigurationException('EiProp is assigned to unknown property: '
						. $this->objectPropertyName, 0, $e), $eiErrorResult);
			}
		}
			
		$entityProperty = null;
		if (null !== $this->entityPropertyName) {
			try {
				$entityProperty = $entityPropertyCollection->getEntityPropertyByName($this->entityPropertyName, true);
			} catch (UnknownEntityPropertyException $e) {
				$this->handleException(new InvalidEiConfigurationException('EiProp is assigned to unknown EntityProperty: '
						. $this->entityPropertyName, 0, $e), $eiErrorResult);
			}
		}

// 		if ($entityProperty !== null || $accessProxy !== null) {
			try {
				$this->eiPropConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
			} catch (IncompatiblePropertyException $e) {
				$this->handleException($e, $eiErrorResult);
			}
// 		}
	}
	
	private function handleException($e, EiErrorResult $eiErrorResult = null) {
		$e = $this->createException($e);
		if (null !== $eiErrorResult) {
			$eiErrorResult->putEiPropError(EiPropError::fromEiProp($this->eiPropConfigurator->getEiComponent(), $e));
			return;
		}
		
		throw $e;
	}
	
	/**
	 * @param \Throwable $e
	 * @return \rocket\ei\component\InvalidEiConfigurationException
	 */
	private function createException($e) {
		$eiComponent = $this->eiPropConfigurator->getEiComponent();
		
		return new InvalidEiConfigurationException('EiProp is invalid configured: ' . $eiComponent . ' in '
				. $eiComponent->getWrapper()->getEiPropCollection()->getEiMask(), 0, $e);
	}
}

class EiCommandSetupTask {
	private $eiCmd;
	private $eiConfigurator;
	
	public function __construct(EiCmdNature $eiCmd, EiConfigurator $eiConfigurator) {
		$this->eiCmd = $eiCmd;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCmdNature
	 */
	public function getEiCommand() {
		return $this->eiCmd;
	}
	
	/**
	 * @return \rocket\ei\component\EiConfigurator
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
	 * @return \rocket\ei\component\modificator\EiModNature
	 */
	public function getEiModificator() {
		return $this->eiModificator;
	}
	
	/**
	 * @return \rocket\ei\component\EiConfigurator
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
	 * @return \rocket\ei\component\prop\EiPropNature
	 */
	public function getEiProp() {
		return $this->eiProp;
	}
	
	/**
	 * @return \rocket\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}
