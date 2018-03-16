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

use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;
use rocket\ei\EiType;
use n2n\util\config\Attributes;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\reflection\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use rocket\ei\component\EiConfigurator;
use rocket\ei\mask\EiMask;
use n2n\persistence\orm\OrmConfigurationException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\config\InvalidConfigurationException;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\command\IndependentEiCommand;
use rocket\ei\component\modificator\IndependentEiModificator;
use rocket\spec\extr\EiTypeExtraction;
use rocket\spec\extr\EiMaskExtraction;
use rocket\spec\extr\EiPropExtraction;
use rocket\spec\extr\EiComponentExtraction;
use rocket\spec\extr\EiTypeExtensionExtraction;
use n2n\l10n\Lstr;
use rocket\spec\extr\EiModificatorExtraction;
use rocket\ei\EiTypeExtension;
use n2n\util\StringUtils;

class EiTypeFactory {
	private $entityModelManager;
	private $setupQueue;
	
	public function __construct(EntityModelManager $entityModelManager, EiSetupQueue $setupQueue) {
		$this->entityModelManager = $entityModelManager;
		$this->setupQueue = $setupQueue;
	}
	/**
	 * @param EiTypeExtraction $eiTypeExtraction
	 * @param EiModificatorExtraction[] $eiModificatorExtractions
	 * @param EiTypeExtensionExtraction[] $eiTypeExtensionExtractions
	 * @return \rocket\ei\EiType
	 */
	public function create(EiTypeExtraction $eiTypeExtraction, array $eiModificatorExtractions) {
		$eiType = null;
		try {
			$eiType = new EiType($eiTypeExtraction->getId(), $eiTypeExtraction->getModuleNamespace());
			$this->asdf($eiTypeExtraction->getEiMaskExtraction(), $eiType->getEiMask(), $eiModificatorExtractions);
		} catch (InvalidConfigurationException $e) {
			throw $this->createEiTypeException($eiTypeExtraction->getId(), $e);
		}
		
		$eiType->setDataSourceName($eiTypeExtraction->getDataSourceName());
		$eiType->setNestedSetStrategy($eiTypeExtraction->getNestedSetStrategy());
		
// 		$eiTypeExtensionCollection = $eiType->getEiTypeExtensionCollection();
// 		foreach ($eiTypeExtraction->getEiTypeExtensionExtractions() as $eiTypeExtensionExtraction) {
// 			try {
// 				$eiTypeExtensionCollection->add($this->createEiTypeExtension($eiType, $eiTypeExtensionExtraction));
// 			} catch (InvalidConfigurationException $e) {
// 				throw $this->createEiTypeException($eiTypeExtraction->getId(),
// 						$this->createEiMaskException($eiTypeExtensionExtraction->getId(), $e));
// 			}
// 		}
		
		
		
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
	
	/**
	 * @param EiMaskExtraction $eiMaskExtraction
	 * @param EiMask $eiMask
	 * @param EiModificatorExtraction[] $eiModificatorExtractions
	 */
	private function asdf(EiMaskExtraction $eiMaskExtraction, EiMask $eiMask, array $eiModificatorExtractions) {
		$eiDef = $eiMask->getDef();
		
		$eiDef->setLabel($eiMaskExtraction->getLabel());
		$eiDef->setPluralLabel($eiMaskExtraction->getPluralLabel());
		$eiDef->setIdentityStringPattern($eiMaskExtraction->getIdentityStringPattern());
		
		if (null !== ($draftingAllowed = $eiMaskExtraction->isDraftingAllowed())) {
			$eiDef->setDraftingAllowed($draftingAllowed);
		}
		$eiDef->setPreviewControllerLookupId($eiMaskExtraction->getPreviewControllerLookupId());
		
		$eiPropCollection = $eiMask->getEiPropCollection();
		foreach ($eiMaskExtraction->getEiPropExtractions() as $eiPropExtraction) {
			try {
				$eiPropCollection->addIndependent($this->createEiProp($eiPropExtraction, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiPropException($eiPropExtraction, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiPropException($eiPropExtraction, $e);
			}
		}
		
		$eiCommandCollection = $eiMask->getEiCommandCollection();
		foreach ($eiMaskExtraction->getEiCommandExtractions() as $eiComponentExtraction) {
			try {
				$eiCommandCollection->addIndependent(
						$this->createEiCommand($eiComponentExtraction, $eiMask));
			} catch (TypeNotFoundException $e) {
				throw $this->createEiCommandException($eiPropExtraction->getId(), $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiCommandException($eiPropExtraction->getId(), $e);
			}
		}
		
		$eiDef->setFilterGroupData($eiMaskExtraction->getFilterGroupData());
		$eiDef->setDefaultSortData($eiMaskExtraction->getDefaultSortData());
		
		foreach ($eiModificatorExtractions as $eiModificatorExtraction) {
			try {
				$eiModificatorCollection->add($this->createEiModificator($eiModificatorExtraction, $eiMask));
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiTypeException($eiTypeExtraction->getId(),
						$this->createEiModificatorException($eiModificatorExtraction->getId(), $e));
			}
		}
		
		$eiMask->setDisplayScheme($eiMaskExtraction->getDisplayScheme());
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
		
		if (!$eiPropClass->implementsInterface('rocket\ei\component\prop\indepenent\IndependentEiProp')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiPropClass->getName()
					. '\' must implement \'rocket\ei\component\prop\indepenent\IndependentEiProp\'.');
		}
		
		$eiProp = $eiPropClass->newInstance();
		$eiProp->setId($id);
		
		$moduleNamespace = null;
		if ($eiMask->isExtension()) {
			$moduleNamespace = $eiMask->getExtension()->getModuleNamespace();
		} else {
			$moduleNamespace = $eiMask->getEiType()->getModuleNamespace();
		}
		$eiProp->setLabelLstr(new Lstr($eiPropExtraction->getLabel() ?? StringUtils::pretty($id), $moduleNamespace));
		
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
		
		$this->setupQueue->addEiConfigurator($eiPropConfigurator);
		
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
		
		if (!$eiCommandClass->implementsInterface('rocket\ei\component\command\IndependentEiCommand')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiCommandClass->getName()
					. '\' must implement \'rocket\ei\component\command\IndependentEiCommand\'.');
		}
		
		$eiCommand = $eiCommandClass->newInstance();
		
		$eiConfigurator = $eiCommand->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, 'rocket\ei\component\EiConfigurator',
				$eiCommand, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setAttributes(new Attributes($configurableExtraction->getProps()));
		$this->setupQueue->addEiConfigurator($eiConfigurator);
		
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
		
		if (!$eiModificatorClass->implementsInterface('rocket\ei\component\modificator\IndependentEiModificator')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiModificatorClass->getName()
					. '\' must implement \'rocket\ei\component\modificator\IndependentEiModificator\'.');
		}
		
		$eiModificator =  $eiModificatorClass->newInstance();
		
		$eiConfigurator = $eiModificator->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, EiConfigurator::class, $eiModificator, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setAttributes(new Attributes($eiModificatorExtraction->getProps()));
		$this->setupQueue->addEiConfigurator($eiConfigurator);
		
		return $eiModificator;
	}
	
	
	/**
	 * @param EiTypeExtensionExtraction $eiTypeExtensionExtraction
	 * @param EiModificatorExtraction[] $eiModificatorExtractions
	 */
	public function createEiTypeExtension(EiMask $extenedEiMask, EiTypeExtensionExtraction $eiTypeExtensionExtraction,
			array $eiModificatorExtractions) {
				
		$eiMask = new EiMask($extenedEiMask->getEiType());
		$eiTypeExtension = new EiTypeExtension($eiTypeExtensionExtraction->getId(),
				$eiTypeExtensionExtraction->getModuleNamespace(),
				$eiMask, $extenedEiMask);
		
		$this->asdf($eiTypeExtensionExtraction->getEiMaskExtraction(), $eiMask, $eiModificatorExtractions);
		
		return $eiTypeExtension;
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