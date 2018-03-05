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
use rocket\spec\ei\EiType;
use n2n\util\config\Attributes;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\reflection\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\spec\ei\component\prop\indepenent\IncompatiblePropertyException;
use rocket\spec\ei\component\EiConfigurator;
use rocket\spec\ei\mask\EiMask;
use n2n\persistence\orm\OrmConfigurationException;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\ei\component\prop\EiProp;
use rocket\spec\ei\component\command\IndependentEiCommand;
use rocket\spec\ei\component\modificator\IndependentEiModificator;
use rocket\spec\config\extr\EiTypeExtraction;
use rocket\spec\config\extr\EiMaskExtraction;
use rocket\spec\config\extr\EiPropExtraction;
use rocket\spec\config\extr\EiComponentExtraction;
use rocket\spec\config\extr\EiTypeExtensionExtraction;
use n2n\l10n\Lstr;
use rocket\spec\config\extr\EiModificatorExtraction;
use rocket\spec\ei\mask\EiTypeExtension;

class EiTypeFactory {
	private $entityModelManager;
	private $setupQueue;
	
	public function __construct(EntityModelManager $entityModelManager, EiTypeSetupQueue $setupQueue) {
		$this->entityModelManager = $entityModelManager;
		$this->setupQueue = $setupQueue;
	}
	/**
	 * @param EiTypeExtraction $eiTypeExtraction
	 * @return \rocket\spec\ei\EiType
	 */
	public function create(EiTypeExtraction $eiTypeExtraction) {
		$eiType = null;
		try {
			$eiType = new EiType($eiTypeExtraction->getId(), $eiTypeExtraction->getModuleNamespace());
			$this->asdf($eiTypeExtraction->getEiMaskExtraction(), $eiType->getEiMask());
		} catch (InvalidConfigurationException $e) {
			throw $this->createEiTypeException($eiTypeExtraction->getId(), $e);
		}
		
		$eiType->setDataSourceName($eiTypeExtraction->getDataSourceName());
		$eiType->setNestedSetStrategy($eiTypeExtraction->getNestedSetStrategy());
		
		$eiMaskExtensionCollection = $eiType->getEiTypeExtensionCollection();
		foreach ($eiTypeExtraction->getEiTypeExtensionExtractions() as $eiMaskExtensionExtraction) {
			try {
				$eiMaskExtensionCollection->add($this->createEiTypeExtension($eiType, $eiMaskExtensionExtraction));
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiTypeException($eiTypeExtraction->getId(),
						$this->createEiMaskException($eiMaskExtensionExtraction->getId(), $e));
			}
		}
		
		$eiModificatorCollection = $eiType->getEiMask()->getEiModificatorCollection();
		foreach ($eiTypeExtraction->getEiModificatorExtractions() as $eiModificatorExtraction) {
			try {
				$eiMask = null;
				if (null !== $eiModificatorExtraction->getEiMaskId()) {
					$eiMask = $eiMaskExtensionCollection->getById($eiModificatorExtraction->getEiMaskId());
				}
				
				$eiModificatorCollection->add($this->createEiModificator($eiModificatorExtraction, $eiType, $eiMask));
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiTypeException($eiTypeExtraction->getId(),
						$this->createEiModificatorException($eiModificatorExtraction->getId(), $e));
			}
		}
		
		return $eiType;
	}
	
	
	private function getEntityModel($entityClassName) {
		try {
			return $this->entityModelManager->getEntityModelByClass(
					ReflectionUtils::createReflectionClass($entityClassName));
		} catch (TypeNotFoundException $e) {
			throw new InvalidSpecConfigurationException('EiType is defined for unknown entity: ' . $entityClassName, 0, $e);
		} catch (OrmConfigurationException $e) {
			throw new InvalidSpecConfigurationException('EiType is defined for invalid entity: ' . $entityClassName, 0, $e);
		}
	}
	
	private function asdf(EiMaskExtraction $eiMaskExtensionExtraction, EiMask $eiMask) {
		$eiDef = $eiMask->getDef();
		
		$eiDef->setLabel($eiMaskExtensionExtraction->getLabel());
		$eiDef->setPluralLabel($eiMaskExtensionExtraction->getPluralLabel());
		$eiDef->setIdentityStringPattern($eiMaskExtensionExtraction->getIdentityStringPattern());
		
		if (null !== ($draftingAllowed = $eiMaskExtensionExtraction->isDraftingAllowed())) {
			$eiDef->setDraftingAllowed($draftingAllowed);
		}
		$eiDef->setPreviewControllerLookupId($eiMaskExtensionExtraction->getPreviewControllerLookupId());
		
		$eiPropCollection = $eiMask->getEiPropCollection();
		foreach ($eiMaskExtensionExtraction->getEiPropExtractions() as $eiPropExtraction) {
			try {
				$eiPropCollection->addIndependent($this->createEiProp($eiPropExtraction, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiPropException($eiPropExtraction, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiPropException($eiPropExtraction, $e);
			}
		}
		
		$eiCommandCollection = $eiMask->getEiCommandCollection();
		foreach ($eiMaskExtensionExtraction->getEiCommandExtractions() as $eiComponentExtraction) {
			try {
				$eiCommandCollection->addIndependent(
						$this->createEiCommand($eiComponentExtraction, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiCommandException($eiPropExtraction->getId(), $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiCommandException($eiPropExtraction->getId(), $e);
			}
		}
		
		$eiDef->setFilterGroupData($eiMaskExtensionExtraction->getFilterGroupData());
		$eiDef->setDefaultSortData($eiMaskExtensionExtraction->getDefaultSortData());
	}
	
	/**
	 * @param EiPropExtraction $eiPropExtraction
	 * @param EiType $eiType
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws IncompatiblePropertyException
	 * @throws TypeNotFoundException
	 * @return EiProp
	 */
	public function createEiProp(EiPropExtraction $eiPropExtraction, EiMask $eiMask) {
		$id = $eiPropExtraction->getId();
		$eiPropClass = ReflectionUtils::createReflectionClass($eiPropExtraction->getClassName());
		
		if (!$eiPropClass->implementsInterface('rocket\spec\ei\component\prop\indepenent\IndependentEiProp')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiPropClass->getName()
					. '\' must implement \'rocket\spec\ei\component\prop\indepenent\IndependentEiProp\'.');
		}
		
		$eiProp = $eiPropClass->newInstance();
		$eiProp->setId($id);
		
		$moduleNamespace = null;
		if ($eiMask->isExtension()) {
			$moduleNamespace = $eiMask->getExtension()->getModuleNamespace();
		} else {
			$moduleNamespace = $eiMask->getEiType()->getModuleNamespace();
		}
		$eiProp->setLabelLstr(new Lstr($eiPropExtraction->getLabel(), $moduleNamespace));
		
		$eiPropConfigurator = $eiProp->createEiPropConfigurator();
		ArgUtils::valTypeReturn($eiPropConfigurator, EiPropConfigurator::class, $eiProp,
				'createEiPropConfigurator');
		IllegalStateException::assertTrue($eiPropConfigurator instanceof EiPropConfigurator);
		$eiPropConfigurator->setAttributes(new Attributes($eiPropExtraction->getProps()));
		
		$objectPropertyName = $eiPropExtraction->getObjectPropertyName();
		$entityPropertyName = $eiPropExtraction->getEntityPropertyName();
		
		$this->setupQueue->addPropIn(new PropIn($eiMask->getEiType(), $eiPropConfigurator, $objectPropertyName, $entityPropertyName));
		
		// 		$this->setupQueue->addClosure(function () use ($eiType, $eiPropConfigurator, $objectPropertyName, $entityPropertyName) {
		// 			$accessProxy = null;
		// 			if (null !== $objectPropertyName) {
		// 				try{
		// 					$propertiesAnalyzer = new PropertiesAnalyzer($eiType->getEntityModel()->getClass(), false);
		// 					$accessProxy = $propertiesAnalyzer->analyzeProperty($objectPropertyName, false, true);
		// 					$accessProxy->setNullReturnAllowed(true);
		// 				} catch (ReflectionException $e) {
		// 					throw new InvalidEiComponentConfigurationException('EiProp is assigned to unknown property: '
		// 							. $objectPropertyName, 0, $e);
		// 				}
		// 			}
		
		// 			$entityProperty = null;
		// 			if (null !== $entityPropertyName) {
		// 				try {
		// 					$entityProperty = $eiType->getEntityModel()->getLevelEntityPropertyByName($entityPropertyName, true);
		// 				} catch (UnknownEntityPropertyException $e) {
		// 					throw new InvalidEiComponentConfigurationException('EiProp is assigned to unknown EntityProperty: '
		// 							. $entityPropertyName, 0, $e);
		// 				}
		// 			}
		
		// 			if ($entityProperty !== null || $accessProxy !== null) {
		// 				$eiPropConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
		// 			}
		// 		});
		
		$this->setupQueue->add($eiPropConfigurator);
		
		return $eiProp;
	}
	
	/**
	 * @param EiComponentExtraction $configurableExtraction
	 * @param EiType $eiType
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws TypeNotFoundException
	 * @return IndependentEiCommand
	 */
	public function createEiCommand(EiComponentExtraction $configurableExtraction, EiMask $eiMask) {
		$eiCommandClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		
		if (!$eiCommandClass->implementsInterface('rocket\spec\ei\component\command\IndependentEiCommand')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiCommandClass->getName()
					. '\' must implement \'rocket\spec\ei\component\command\IndependentEiCommand\'.');
		}
		
		$eiCommand = $eiCommandClass->newInstance();
		
		$eiConfigurator = $eiCommand->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, 'rocket\spec\ei\component\EiConfigurator',
				$eiCommand, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setAttributes(new Attributes($configurableExtraction->getProps()));
		$this->setupQueue->add($eiConfigurator);
		
		return $eiCommand;
	}
	
	/**
	 * @param EiComponentExtraction $eiModificatorExtraction
	 * @param EiType $eiType
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws TypeNotFoundException
	 * @return IndependentEiModificator
	 */
	public function createEiModificator(EiModificatorExtraction $eiModificatorExtraction, EiType $eiType, EiMask $eiMask = null) {
		$eiModificatorClass = ReflectionUtils::createReflectionClass($eiModificatorExtraction->getClassName());
		
		if (!$eiModificatorClass->implementsInterface('rocket\spec\ei\component\modificator\IndependentEiModificator')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiModificatorClass->getName()
					. '\' must implement \'rocket\spec\ei\component\modificator\IndependentEiModificator\'.');
		}
		
		$eiModificator =  $eiModificatorClass->newInstance();
		
		$eiConfigurator = $eiModificator->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, EiConfigurator::class, $eiModificator, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setAttributes(new Attributes($eiModificatorExtraction->getProps()));
		$this->setupQueue->add($eiConfigurator);
		
		return $eiModificator;
	}
	
	public function createEiTypeExtension(EiType $eiType, EiTypeExtensionExtraction $eiMaskExtensionExtraction) {
		$eiMask = new EiMask($eiType);
		$eiMaskExtension = new EiTypeExtension($eiMaskExtensionExtraction->getId(),
				$eiMaskExtensionExtraction->getModuleNamespace(),
				$eiMask, $eiType->getEiMask());
		
		$this->asdf($eiMaskExtensionExtraction->getEiMaskExtraction(), $eiMask);
		
		return $eiMaskExtension;
	}
	
	private function createEiTypeException($eiTypeId, \Exception $previous) {
		return new InvalidSpecConfigurationException('Could not create EiType (id: ' . $eiTypeId . ').', 0, $previous);
	}
	
	private function createEiPropException(EiPropExtraction $eiPropExtraction, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create ' . $eiPropExtraction->getClassName()
				. ' [id: ' . $eiPropExtraction->getId() . '].', 0, $previous);
	}
	
	private function createEiCommandException($eiCommandId, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create EiCommand (id: ' . $eiCommandId . ').', 0, $previous);
	}
	
	private function createEiModificatorException($eiModificatorId, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create EiModificatior (id: ' . $eiModificatorId . ').', 0, $previous);
	}
	
	private function createEiMaskException($eiMaskId, \Exception $previous) {
		return new InvalidSpecConfigurationException('Could not create EiMask (id: ' . $eiMaskId . ').', 0, $previous);
	}	
}