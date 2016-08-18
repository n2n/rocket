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

use rocket\spec\ei\component\field\InvalidEiFieldConfigurationException;
use rocket\spec\ei\EiSpec;
use n2n\core\module\Module;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\ReflectionUtils;
use n2n\io\IoUtils;
use rocket\spec\ei\component\field\InvalidEiCommandConfigurationException;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\component\field\EiFieldOperationFailedException;
use rocket\spec\config\extr\SpecExtraction;
use n2n\l10n\Message;
use rocket\spec\config\source\RocketConfigSource;
use rocket\spec\core\ManageConfig;
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
use n2n\util\config\InvalidConfigurationException;
use n2n\util\config\AttributesException;

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
		
		try {
			return $this->menuItems[$id] = $this->createMenuItem($this->specExtractionManager
					->getMenuItemExtractionById($id));
		} catch (UnknownSpecException $e) {
			throw $this->createInvalidMenuItemConfigurationException($id, $e);
		} catch (UnknownEiMaskException $e)  {
			throw $this->createInvalidMenuItemConfigurationException($id, $e);
		}
		
	}
	
	private function createInvalidMenuItemConfigurationException($menuItemId, \Exception $previous) {
		throw new InvalidMenuItemConfigurationException('MenuItem with following id invalid configured: ' 
				. $menuItemId, 0, $previous);
	}
	
	private function createMenuItem(MenuItemExtraction $menuItemExtraction) {
		$spec = $this->getSpecById($menuItemExtraction->getSpecId());
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
	
	/**
	 * 
	 * @param string $id
	 * @return Spec
	 * @throws UnknownSpecException
	 * @throws InvalidSpecConfigurationException
	 */
	public function getSpecById($id): Spec {
		if (isset($this->specs[$id])) {
			return $this->specs[$id];
		}

		return $this->createSpecFromExtr($this->specExtractionManager->getSpecExtractionById($id));
	}
	
	private function createSpecFromExtr(SpecExtraction $specExtraction): Spec {
		if ($specExtraction instanceof CustomSpecExtraction) {
			return $this->specs[$specExtraction->getId()] = CustomSpecFactory::create($specExtraction);
		} 
		
		$eiSpec = (new EiSpecFactory($this->entityModelManager, $this->getEiSpecSetupQueue()))
				->create($specExtraction);
				
		$this->specs[$specExtraction->getId()] = $eiSpec;
		$this->eiSpecs[$eiSpec->getEntityModel()->getClass()->getName()] = $eiSpec;
		
		if ($eiSpec->getEntityModel()->hasSuperEntityModel()) {
			$eiSpec->setSuperEiSpec($this->getEiSpecByClass(
					$eiSpec->getEntityModel()->getSuperEntityModel()->getClass()));
		}
			
		foreach ($eiSpec->getEntityModel()->getSubEntityModels() as $subEntityModel) {
			if ($this->containsEiSpecClass($subEntityModel->getClass())) {
				$this->getEiSpecByClass($subEntityModel->getClass());
			}
		}
		
		$this->getEiSpecSetupQueue()->trigger();
		
		return $eiSpec;
	}
	
	private function initAll() {
		foreach ($this->specExtractionManager->getSpecIds() as $specId) {
			$this->getSpecById($specId);
		}
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getEiSpecByClass(\ReflectionClass $class) {
		$this->initAll();
		
		$className = $class->getName();
		if (isset($this->eiSpecs[$className])) {
			return $this->eiSpecs[$className];
		}
		
		return $this->createSpecFromExtr($this->specExtractionManager->getEiSpecExtractionByClassName($className));
	}
	
	public function containsEiSpecClass(\ReflectionClass $class) {
		return $this->specExtractionManager->containsEiSpecEntityClassName($class->getName());
	}
	
	public function getEiSpecs(): array {
		$eiSpecs = array();
		foreach ($this->specExtractionManager->getSpecIds() as $specId) {
			$spec = $this->getSpecById($specId);
			if ($spec instanceof EiSpec) {
				$eiSpecs[$specId] = $spec;
			}
		}
		return $eiSpecs;
	}
	
	public function getCustomSpecs(): array {
		$customSpecs = array();
		foreach ($this->specExtractionManager->getSpecIds() as $specId) {
			$spec = $this->getSpecById($specId);
			if ($spec instanceof CustomSpec) {
				$customSpecs[$specId] = $spec;
			}
		}
		return $customSpecs;
	}
	
// 	private function extractEiSpecByClass(\ReflectionClass $class) {
// 		$eiSpecExtraction = $this->extractEiSpec($class);
// 		$eiSpec = $eiSpecExtraction->createScript($this);
		
// 		$this->specs[$eiSpec->getId()] = $eiSpec;
// 		$this->eiSpecs[$class->getName()] = $eiSpec;
		
// 		if ($eiSpec->getEntityModel()->hasSuperEntityModel()) {
// 			$eiSpec->setSuperEiSpec($this->getEiSpecByClass(
// 					$eiSpec->getEntityModel()->getSuperEntityModel()->getClass()));
// 		}
		
// 		foreach ($eiSpec->getEntityModel()->getSubEntityModels() as $subEntityModel) {
// 			if (!$this->containsEiSpecClass($subEntityModel->getClass())) continue;
// 			$this->getEiSpecByClass($subEntityModel->getClass());
// 		}
		
// // 		$this->extractEiMasks($eiSpec, $eiSpecExtraction);
		
// 		return $eiSpec;
// 	}
	
	
// 	private function extractEiMasks(EiSpec $eiSpec, EiSpecExtraction $eiSpecExtraction) {
// 		$maskSet = $eiSpec->getEiMaskSet();

// 		$eiSpecFactory = new EiSpecFactory($this->entityModelManager, $this->eiSpecSetupQueue);
		
// 		foreach ($this->specExtractionManager->$scriptConfig->extractEiMasks($eiSpec->getId()) as $maskExtraction) {
// 			$maskSet->add($eiSpecFactory->createEiMask($eiSpec, $eiSpecExtraction, $maskExtraction));
// 		}
		
		
		
// 	}
	
	/**
	 * @param string $id
	 * @throws UnknownSpecException
	 * @throws InvalidSpecConfigurationException
	 * @return EiSpec
	 */
	public function getEiSpecById($id) {
		$script = $this->getSpecById($id);
		if ($script instanceof EiSpec) {
			return $script;
		}
	
		throw new UnknownSpecException('Script with id  \'' . $id . '\' is no EiSpec');
	}

	public function getSpecs(): array {
		$specs = array();
		foreach ($this->specExtractionManager->getSpecIds() as $specId) {
			$specs[$specId] = $this->getSpecById($specId);
		}
		return $specs;
	}
	
	
// 	private function generateSpecId($module, $id) {
// 		$prefix = ReflectionUtils::encodeNamespace((string) $module, '-');
// 		$baseId = mb_strtolower($prefix . '-' . IoUtils::stripSpecialChars($id));
// 		$id = $baseId;
// 		$ext = 0;
// 		while ($this->containsSpecId($id)) {
// 			$id = $baseId . ($ext++);
// 		}
// 		return $id;
// 	}
	
// 	public function createCustomSpec(string $id, string $moduleNamespace, \ReflectionClass $controllerClass) {
		
// 		$this->specExtractionManager->addSpec($specExtraction)
// 	}
	
	
// 	public function putSpec(Spec $spec) {
// 		$this->specs[$spec->getId()] = $spec;
		
// 		if ($spec instanceof EiSpec) {
// 			$this->eiSpecs[$spec->getEntityModel()->getClass()->getName()] = $spec;
// 		}
		
// 		$this->specExtractionManager->addSpec($spec->toSpecExtraction());
// 	}
// 	/**
// 	 * @param Module $module
// 	 * @param unknown_type $idBase
// 	 * @param unknown_type $label
// 	 * @param EntityModel $entityModel
// 	 * @return EiSpec
// 	 */
// 	public function createEiSpec(Module $module, $idBase, $label, $pluralLabel, EntityModel $entityModel) {
// 		if ($this->containsEiSpecClass($entityModel->getClass())) {
// 			throw new \InvalidArgumentException('An EiSpec for class \'' . $entityModel->getClass()->getName() 
// 					. '\' is already defined.');
// 		}
		
// 		$id = $this->generateSpecId($module, $idBase);
// 		$eiSpec = new EiSpec($id, $label, $pluralLabel, $module, $entityModel);
		
// 		if ($entityModel->hasSuperEntityModel()) {
// 			$this->eiSpecSetupQueue->setLenient(true);
// 			$superEiSpec = $this->getEiSpecByClass($entityModel->getSuperEntityModel()->getClass());
// 			$eiSpec->setSuperEiSpec($superEiSpec);
// 			$eiSpec->setIdentityStringPattern($superEiSpec->getIdentityStringPattern());
// 		}
		
// // 		$eiFieldsFinder = $this->getEiComponentStore()->createEiFieldManager($eiSpec);
// 		$identityStringPattern = '';
// 		$idPlaceHolder = 'unknown';
// // 		foreach ($entityModel->getLevelProperties() as $property) {
// // 			$eiField = $eiFieldsFinder->suggestObjectPropertyEiField($property);
// // 			$eiSpec->getEiFieldCollection()->add($eiField);
			
// // 			if (!($eiField instanceof HighlightableEiField) || $entityModel->hasSuperEntityModel()) continue;

// // 			if ($property instanceof IdProperty)  {
// // 				$idPlaceHolder = EiSpec::KNOWN_STRING_FIELD_OPEN_DELIMITER
// // 						. $eiField->getId() . EiSpec::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
// // 			} else if (!mb_strlen($identityStringPattern)) {
// // 				$identityStringPattern = EiSpec::KNOWN_STRING_FIELD_OPEN_DELIMITER
// // 						. $eiField->getId() . EiSpec::KNOWN_STRING_FIELD_CLOSE_DELIMITER . ' ';
// // 			}

// // 		}

// // 		if (!$entityModel->hasSuperEntityModel()) {
// 			$identityStringPattern .= '[' . $idPlaceHolder . ']';
// 			$eiSpec->setIdentityStringPattern($identityStringPattern);
// // 		}

// 		$this->specs[$id] = $eiSpec;
// 		$this->eiSpecs[$entityModel->getClass()->getName()] = $eiSpec;
// // 		$this->initializeEiSpec($eiSpec, true);
		
// 		$scriptConfig = $this->getOrCreateSpecConfig($module);
// 		$scriptConfig->putSpecExtraction($eiSpec->toSpecExtraction());
		
// 		$this->manageConfig->registerAsUnsealed($id);

// 		return $eiSpec;
// 	}
	
// 	public function createCustomSpec(Module $module, $idBase, $label, \ReflectionClass $controllerClass) {
// 		$id = $this->generateSpecId($module, $idBase);
// 		$script = new CustomSpec($id, $label, $module, $controllerClass);
// 		$this->specs[$id] = $script;
// 		return $script;
// 	}
	
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
		if ($this->eiSpecSetupQueue === null) {
			throw new IllegalStateException('No EiSpecSetupQueue not set.');
		}
		
		return $this->eiSpecSetupQueue;
	}
	
	public function setEiSpecSetupQueue(EiSpecSetupQueue $eiSpecSetupQueue) {
		$this->eiSpecSetupQueue = $eiSpecSetupQueue;
	}
		
// 	public static function createInvalidSpecConfigurationException($scriptId, \Exception $previous = null, $reason = null) {
// 		if ($reason === null && isset($previous)) {
// 			$reason = $previous->getMessage();
// 		}
		
// 		return new InvalidSpecConfigurationException('Configruation of script with id \'' . $scriptId  
// 						. '\' is invalid.' . ($reason !== null ? ' Reason: ' . $reason : ''), 
// 				0, $previous);
// 	}
	
// 	public static function createInvalidEiFieldConfigurationException($eiFieldId, \Exception $previous = null, $reason = null) {
// 		if ($reason === null && $previous !== null) {
// 			$reason = $previous->getMessage();
// 		}
	
// 		return new InvalidEiFieldConfigurationException('Configruation of script field with id \'' 
// 						. $eiFieldId  . '\' is invalid.' . ($reason !== null ? ' Reason: ' . $reason : ''), 
// 				0, $previous);
// 	}
	
// 	public static function createInvalidEiCommandConfigurationException($className, \Exception $previous = null, $reason = null) {
// 		if ($reason === null && isset($previous)) {
// 			$reason = $previous->getMessage();
// 		}
	
// 		return new InvalidEiCommandConfigurationException(
// 				'Configruation of EiCommand \'' . $className  . '\' is invalid.'
// 				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
// 	}
	
// 	public static function createInvalidEiModificatorConfigurationException($className, \Exception $previous = null, $reason = null) {
// 		if ($reason === null && isset($previous)) {
// 			$reason = $previous->getMessage();
// 		}
	
// 		return new InvalidEiCommandConfigurationException(
// 				'Configruation of EiModificator \'' . $className  . '\' is invalid.'
// 				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
// 	}
	
// 	public static function createInvalidScriptListenerConfigurationException($className, \Exception $previous = null, $reason = null) {
// 		if ($reason === null && isset($previous)) {
// 			$reason = $previous->getMessage();
// 		}
	
// 		return new InvalidEiCommandConfigurationException(
// 				'Configruation of ScriptListener \'' . $className  . '\' is invalid.'
// 				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
// 	}
	
// 	public static function createEiFieldOperationFailedException(EiField $eiField, \Exception $previous = null, $reason = null) {
// 		if ($reason === null && isset($previous)) {
// 			$reason = $previous->getMessage();
// 		}
		
// 		return new EiFieldOperationFailedException(
// 				'Error occurred in EiField \'' . $eiField->getLabel()  . '\' (EiSpec \'' . $eiField->getEiSpec()->getLabel() . '\').'
// 				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
// 	}
}

class EiSpecSetupQueue {
	private $scriptManager;
	private $n2nContext;
// 	private $lenient;
	private $eiConfigurator = array();
	private $es = array();
	private $voidModeEnabled = false;
	
	private $running = false;
	
	public function __construct(SpecManager $scriptManager, N2nContext $n2nContext) {
		$this->scriptManager = $scriptManager;
		$this->n2nContext = $n2nContext;
	}
	
// 	public function isLenient() {
// 		return $this->lenient;
// 	}
	
// 	public function setLenient($lenient) {
// 		$this->lenient = (boolean) $lenient;
// 	}
	
	public function isRunning() {
		return $this->running;
	}
	
	public function getN2nContext() {
		return $this->n2nContext;
	}
	
	public function add(EiConfigurator $eiConfigurator) {
		$this->eiConfigurator[] = $eiConfigurator;
	}
	
	public function setVoidModeEnabled(bool $voidModeEnabled) {
		$this->voidModeEnabled = $voidModeEnabled;
	}
	
	public function isVoidModeEnabled(): bool {
		return $this->voidModeEnabled;
	}
	
	public function trigger() {
		if ($this->voidModeEnabled || $this->running) return;
		$this->running = true;
		
		while (null !== ($assignation = array_shift($this->eiConfigurator))) {
			$eiComponent = $assignation->getEiComponent();
			$eiSetupProcess = new SpecEiSetupProcess($this->scriptManager, $this->n2nContext, $eiComponent);
			try {
				$assignation->setup($eiSetupProcess);
				continue;
			} catch (AttributesException $e) {
				throw $eiSetupProcess->createException(null, $e);
			}
			
			$eiSpecId = $eiComponent->getEiSpec()->getId();
			if (!isset($this->es[$eiSpecId])) {
				$this->es[$eiSpecId] = array();
			}
			$this->es[$eiSpecId][] = $e;
		}
		
		$this->running = false;
	}
	
	public function buildErrorMessages($eiSpecId) {
		$errorMessages = array();
		if (isset($this->es[$eiSpecId])) {
			foreach ($this->es[$eiSpecId] as $e) {
				$errorMessages[] = new Message($e->getMessage());
			}
		}
		return $errorMessages;
	}
}

class LenientResult {
	private $eiSpec;
	private $errorMessages;
	
	public function __construct(EiSpec $eiSpec, array $errorMessages) {
		$this->eiSpec = $eiSpec;
		$this->errorMessages = $errorMessages;
	}
	
	public function getEiSpec() {
		return $this->eiSpec;
	}
	
	public function getErrorMessages() {
		return $this->errorMessages;
	}
}
