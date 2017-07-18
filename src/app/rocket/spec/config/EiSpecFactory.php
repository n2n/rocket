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

use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\EiSpec;
use n2n\util\config\Attributes;
use n2n\persistence\orm\model\EntityModelManager;
use rocket\spec\config\mask\CommonEiMask;
use n2n\reflection\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\component\field\indepenent\IncompatiblePropertyException;
use rocket\spec\config\EiSpecSetupQueue;
use rocket\spec\ei\component\EiConfigurator;
use rocket\spec\ei\EiDef;
use rocket\spec\ei\mask\EiMask;
use n2n\persistence\orm\OrmConfigurationException;
use rocket\spec\config\InvalidSpecConfigurationException;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\component\command\IndependentEiCommand;
use rocket\spec\ei\component\modificator\IndependentEiModificator;
use rocket\spec\config\extr\EiSpecExtraction;
use rocket\spec\config\extr\EiDefExtraction;
use rocket\spec\config\extr\EiFieldExtraction;
use rocket\spec\config\extr\EiComponentExtraction;
use rocket\spec\config\extr\CommonEiMaskExtraction;
use rocket\spec\ei\EiEngine;
use n2n\l10n\Lstr;

class EiSpecFactory {
	private $entityModelManager;
	private $setupQueue;
	
	public function __construct(EntityModelManager $entityModelManager, EiSpecSetupQueue $setupQueue) {
		$this->entityModelManager = $entityModelManager;
		$this->setupQueue = $setupQueue;
	}	
	/**
	 * @param EiSpecExtraction $eiSpecExtraction
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function create(EiSpecExtraction $eiSpecExtraction) {
		$eiSpec = null;
		try {
			$eiSpec = new EiSpec($eiSpecExtraction->getId(), $eiSpecExtraction->getModuleNamespace());
			$this->asdf($eiSpecExtraction->getEiDefExtraction(), $eiSpec->getDefaultEiDef(), $eiSpec->getEiEngine(), 
					$eiSpec, null);
		} catch (InvalidConfigurationException $e) {
			throw $this->createEiSpecException($eiSpecExtraction->getId(), $e);
		}

		$eiSpec->setDataSourceName($eiSpecExtraction->getDataSourceName());
		$eiSpec->setNestedSetStrategy($eiSpecExtraction->getNestedSetStrategy());
		
		$eiMaskCollection = $eiSpec->getEiMaskCollection();
		foreach ($eiSpecExtraction->getCommonEiMaskExtractions() as $commonEiMaskExtraction) {
			try {
				$eiMaskCollection->addCommon($this->createCommonEiMask($eiSpec, $commonEiMaskExtraction));
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiSpecException($eiSpecExtraction->getId(), 
						$this->createCommonEiMaskException($commonEiMaskExtraction->getId(), $e));
			}
		}
		$eiMaskCollection->setDefaultId($eiSpecExtraction->getDefaultEiMaskId());
			
		return $eiSpec;
	}
	
	
	private function getEntityModel($entityClassName) {
		try {
			return $this->entityModelManager->getEntityModelByClass(
					ReflectionUtils::createReflectionClass($entityClassName));
		} catch (TypeNotFoundException $e) {
			throw new InvalidSpecConfigurationException('EiSpec is defined for unknown entity: ' . $entityClassName, 0, $e);
		} catch (OrmConfigurationException $e) {
			throw new InvalidSpecConfigurationException('EiSpec is defined for invalid entity: ' . $entityClassName, 0, $e);
		}
	}
	
	private function asdf(EiDefExtraction $eiDefExtraction, EiDef $eiDef, EiEngine $eiEngine, EiSpec $eiSpec, 
			EiMask $eiMask = null) {
		$eiDef->setLabel($eiDefExtraction->getLabel());
		$eiDef->setPluralLabel($eiDefExtraction->getPluralLabel());
		$eiDef->setIdentityStringPattern($eiDefExtraction->getIdentityStringPattern());

		if (null !== ($draftingAllowed = $eiDefExtraction->isDraftingAllowed())) {
			$eiDef->setDraftingAllowed($draftingAllowed);
		}
		$eiDef->setPreviewControllerLookupId($eiDefExtraction->getPreviewControllerLookupId());
		
		$eiFieldCollection = $eiEngine->getEiFieldCollection();
		foreach ($eiDefExtraction->getEiFieldExtractions() as $eiFieldExtraction) {
			try {
				$eiFieldCollection->addIndependent($this->createEiField($eiFieldExtraction, $eiSpec, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiFieldException($eiFieldExtraction, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiFieldException($eiFieldExtraction, $e);
			}
		}

		$eiCommandCollection = $eiEngine->getEiCommandCollection();
		foreach ($eiDefExtraction->getEiCommandExtractions() as $eiComponentExtraction) {
			try {
				$eiCommandCollection->addIndependent(
						$this->createEiCommand($eiComponentExtraction, $eiSpec, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiCommandException($eiFieldExtraction->getId(), $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiCommandException($eiFieldExtraction->getId(), $e);
			}
		}
		
		$eiModificatorCollection = $eiEngine->getEiModificatorCollection();
		foreach ($eiDefExtraction->getEiModificatorExtractions() as $eiComponentExtraction) {
			try {
				$eiModificatorCollection->addIndependent(
						$this->createEiModificator($eiComponentExtraction, $eiSpec, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiModificatorException($eiComponentExtraction->getId(), $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiModificatorException($eiComponentExtraction->getId(), $e);
			}
		}
		
		$eiDef->setFilterGroupData($eiDefExtraction->getFilterGroupData());
		$eiDef->setDefaultSortData($eiDefExtraction->getDefaultSortData());		
	}
	
	/**
	 * @param EiFieldExtraction $eiFieldExtraction
	 * @param EiSpec $eiSpec
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws IncompatiblePropertyException
	 * @throws TypeNotFoundException
	 * @return EiField
	 */
	public function createEiField(EiFieldExtraction $eiFieldExtraction, EiSpec $eiSpec, EiMask $eiMask = null) {
		$id = $eiFieldExtraction->getId();
		$eiFieldClass = ReflectionUtils::createReflectionClass($eiFieldExtraction->getClassName());
		
		if (!$eiFieldClass->implementsInterface('rocket\spec\ei\component\field\indepenent\IndependentEiField')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiFieldClass->getName() 
					. '\' must implement \'rocket\spec\ei\component\field\indepenent\IndependentEiField\'.');
		}
		
		$eiField = $eiFieldClass->newInstance();
		$eiField->setId($id);
		
		$moduleNamespace = null;
		if ($eiMask === null) {
			$moduleNamespace = $eiSpec->getModuleNamespace();
		} else {
			$moduleNamespace = $eiMask->getModuleNamespace();
		}
		$eiField->setLabelLstr(new Lstr($eiFieldExtraction->getLabel(), $moduleNamespace));
		
		$eiFieldConfigurator = $eiField->createEiFieldConfigurator();
		ArgUtils::valTypeReturn($eiFieldConfigurator, EiFieldConfigurator::class, $eiField, 
				'createEiFieldConfigurator');
		IllegalStateException::assertTrue($eiFieldConfigurator instanceof EiFieldConfigurator);
		$eiFieldConfigurator->setAttributes(new Attributes($eiFieldExtraction->getProps()));
		
		$objectPropertyName = $eiFieldExtraction->getObjectPropertyName();
		$entityPropertyName = $eiFieldExtraction->getEntityPropertyName();
		
		$this->setupQueue->addPropIn(new PropIn($eiSpec, $eiFieldConfigurator, $objectPropertyName, $entityPropertyName));
		
// 		$this->setupQueue->addClosure(function () use ($eiSpec, $eiFieldConfigurator, $objectPropertyName, $entityPropertyName) {
// 			$accessProxy = null;
// 			if (null !== $objectPropertyName) {
// 				try{
// 					$propertiesAnalyzer = new PropertiesAnalyzer($eiSpec->getEntityModel()->getClass(), false);
// 					$accessProxy = $propertiesAnalyzer->analyzeProperty($objectPropertyName, false, true);
// 					$accessProxy->setNullReturnAllowed(true);
// 				} catch (ReflectionException $e) {
// 					throw new InvalidEiComponentConfigurationException('EiField is assigned to unknown property: ' 
// 							. $objectPropertyName, 0, $e);
// 				}
// 			}
			
// 			$entityProperty = null;
// 			if (null !== $entityPropertyName) {
// 				try {
// 					$entityProperty = $eiSpec->getEntityModel()->getLevelEntityPropertyByName($entityPropertyName, true);
// 				} catch (UnknownEntityPropertyException $e) {
// 					throw new InvalidEiComponentConfigurationException('EiField is assigned to unknown EntityProperty: ' 
// 							. $entityPropertyName, 0, $e);
// 				}
// 			}
	
// 			if ($entityProperty !== null || $accessProxy !== null) {
// 				$eiFieldConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
// 			}
// 		});
		
		$this->setupQueue->add($eiFieldConfigurator);
		
		return $eiField;
	}
	
	/**
	 * @param EiComponentExtraction $configurableExtraction
	 * @param EiSpec $eiSpec
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws TypeNotFoundException
	 * @return IndependentEiCommand
	 */
	public function createEiCommand(EiComponentExtraction $configurableExtraction, EiSpec $eiSpec, EiMask $eiMask = null) {
		$eiCommandClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		
		if (!$eiCommandClass->implementsInterface('rocket\spec\ei\component\command\IndependentEiCommand')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiCommandClass->getName() 
					. '\' must implement \'rocket\spec\ei\component\command\IndependentEiCommand\'.');
		}
		
		$eiCommand = $eiCommandClass->newInstance();
// 		if ($eiMask === null) {
// 			$eiCommand->setEiThing($eiSpec);
// 		} else {
// 			$eiCommand->setEiMask($eiMask);
// 		}

		$eiConfigurator = $eiCommand->createEiConfigurator();	
		ArgUtils::valTypeReturn($eiConfigurator, 'rocket\spec\ei\component\EiConfigurator',
				$eiCommand, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setAttributes(new Attributes($configurableExtraction->getProps()));
		$this->setupQueue->add($eiConfigurator);
		
		return $eiCommand;
	}
	
	/**
	 * @param EiComponentExtraction $configurableExtraction
	 * @param EiSpec $eiSpec
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws TypeNotFoundException
	 * @return IndependentEiModificator
	 */
	public function createEiModificator(EiComponentExtraction $configurableExtraction, EiSpec $eiSpec, EiMask $eiMask = null) {
		$eiModificatorClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		
		if (!$eiModificatorClass->implementsInterface('rocket\spec\ei\component\modificator\IndependentEiModificator')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiModificatorClass->getName() 
					. '\' must implement \'rocket\spec\ei\component\modificator\IndependentEiModificator\'.');
		}
		
		$eiModificator =  $eiModificatorClass->newInstance();
		$eiModificator->setEiSpec($eiSpec);

		$eiConfigurator = $eiModificator->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, 'rocket\spec\ei\component\EiConfigurator',
				$eiModificator, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiFieldConfigurator);
		$eiConfigurator->setAttributes(new Attributes($eiModificator->getProps()));
		$this->setupQueue->add($eiConfigurator);
		
		return $eiModificator;
	}	
	
	public function createCommonEiMask(EiSpec $eiSpec, CommonEiMaskExtraction $commonEiMaskExtraction): CommonEiMask {
		$commonEiMask = new CommonEiMask($eiSpec, $eiSpec->getModuleNamespace(), $commonEiMaskExtraction->getGuiOrder());
		$commonEiMask->setId($commonEiMaskExtraction->getId());
		
		$this->asdf($commonEiMaskExtraction->getEiDefExtraction(), $commonEiMask->getEiDef(), 
				$commonEiMask->getEiEngine(), $eiSpec, $commonEiMask);
				
		return $commonEiMask;
	}
	
	private function createEiSpecException($eiSpecId, \Exception $previous) {
		return new InvalidSpecConfigurationException('Could not create EiSpec (id: ' . $eiSpecId . ').', 0, $previous);
	}
	
	private function createEiFieldException(EiFieldExtraction $eiFieldExtraction, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create ' . $eiFieldExtraction->getClassName() 
				. ' [id: ' . $eiFieldExtraction->getId() . '].', 0, $previous);
	}
	
	private function createEiCommandException($eiCommandId, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create EiCommand (id: ' . $eiCommandId . ').', 0, $previous);
	}
	
	private function createEiModificatorException($eiModificatorId, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create EiModificatior (id: ' . $eiModificatorId . ').', 0, $previous);
	}
	
	private function createCommonEiMaskException($commonEiMaskId, \Exception $previous) {
		return new InvalidSpecConfigurationException('Could not create CommonEiMask (id: ' . $commonEiMaskId . ').', 0, $previous);
	}
	
}
