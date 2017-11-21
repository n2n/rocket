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
namespace rocket\spec\config\extr;

use rocket\spec\ei\EiType;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\EiDef;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\spec\config\UnknownMaskException;

class EiTypeExtraction extends SpecExtraction {
	private $entityClassName;
	private $eiDefExtraction;
	private $dataSourceName;
	private $nestedSetStrategy;
	private $defaultEiMaskId;
	private $commonEiMaskExtractions = array();
	private $eiModificatorExtractions = array();

	public function getDataSourceName() {
		return $this->dataSourceName;
	}

	public function setDataSourceName($dataSourceName) {
		$this->dataSourceName = $dataSourceName;
	}
	
	/**
	 * @param NestedSetStrategy $nestedSetStrategy
	 */
	public function setNestedSetStrategy(NestedSetStrategy $nestedSetStrategy = null) {
		$this->nestedSetStrategy = $nestedSetStrategy;
	}
	
	/**
	 * @return \n2n\persistence\orm\util\NestedSetStrategy
	 */
	public function getNestedSetStrategy() {
		return $this->nestedSetStrategy;
	}

	public function getEntityClassName() {
		return $this->entityClassName;
	}

	public function setEntityClassName($entityClassName) {
		$this->entityClassName = $entityClassName;
	}
	
	public function getEiDefExtraction() {
		return $this->eiDefExtraction;
	}
	
	public function setEiDefExtraction(EiDefExtraction $eiDefExtraction) {
		$this->eiDefExtraction = $eiDefExtraction;
	}
	
// 	public function isDraftDisabled() {
// 		return $this->draftDisabled;
// 	}

// 	public function setDraftDisabled($draftDisabled) {
// 		$this->draftDisabled = $draftDisabled;
// 	}
	
	public function getDefaultEiMaskId() {
		return $this->defaultEiMaskId;
	}
	
	public function setDefaultEiMaskId(string $defaultEiMaskId = null) {
		$this->defaultEiMaskId = $defaultEiMaskId;
	}
	
	public function addCommonEiMaskExtraction(CommonEiMaskExtraction $commonEiMaskExtraction) {
		$this->commonEiMaskExtractions[$commonEiMaskExtraction->getId()] = $commonEiMaskExtraction;
	}
	
	public function setCommonEiMaskExtractions(array $commonEiMaskExtractions) {
		ArgUtils::valArray($commonEiMaskExtractions, CommonEiMaskExtraction::class);
		$this->commonEiMaskExtractions = $commonEiMaskExtractions;
	}
	

	public function containsCommonEiMaskExtractionId($commonEiMaskExtractionId): bool {
		return isset($this->commonEiMaskExtractions[$commonEiMaskExtractionId]);
	}
	
	public function getCommonEiMaskExtractionById($commonEiMaskExtractionId): CommonEiMaskExtraction {
		if (isset($this->commonEiMaskExtractions[$commonEiMaskExtractionId])) {
			return $this->commonEiMaskExtractions[$commonEiMaskExtractionId];
		}
		
		throw new UnknownMaskException('No EiMask with id \'' . $commonEiMaskExtractionId . '\' defined in: ' 
				. $this->toSpecString());
	}
	
	/**
	 * @return \rocket\spec\config\extr\CommonEiMaskExtraction[]
	 */
	public function getCommonEiMaskExtractions() {
		return $this->commonEiMaskExtractions;
	}
	
	public function addEiModificatorExtraction(EiComponentExtraction $eiModificatorExtraction) {
		$this->eiModificatorExtractions[$eiModificatorExtraction->getId()] = $eiModificatorExtraction;
	}
	
	public function setEiModificatorExtractions(array $eiModificatorExtractions) {
		ArgUtils::valArray($eiModificatorExtractions, EiModificatorExtraction::class);
		$this->eiModificatorExtractions = $eiModificatorExtractions;
	}
	

	public function containsEiModificatorEExtractionId($eiModificatorExtractionId): bool {
		return isset($this->eiModificatorExtractions[$eiModificatorExtractionId]);
	}
	
	public function getEiModificatorExtractionById($eiModificatorExtractionId): CommonEiMaskExtraction {
		if (isset($this->eiModificatorExtractions[$eiModificatorExtractionId])) {
			return $this->eiModificatorExtractions[$eiModificatorExtractionId];
		}
		
		throw new UnknownMaskException('No EiModificator with id \'' . $eiModificatorExtractionId . '\' defined in: ' 
				. $this->toSpecString());
	}
	
	/**
	 * @return \rocket\spec\config\extr\EiModificatorExtraction[]
	 */
	public function getEiModificatorExtractions() {
		return $this->eiModificatorExtractions;
	}
	
// 	public function createScript(SpecManager $specManager) {
// 		$factory = new EiTypeFactory($specManager->getEntityModelManager(), $specManager->getEiTypeSetupQueue());
// 		return $factory->create($this);
// 	}
	
// 	public static function createFromEiType(EiType $eiType) {
// 		$extraction = new EiTypeExtraction($eiType->getId(), $eiType->getModuleNamespace());
// 		$extraction->setEntityClassName($eiType->getEntityModel()->getClass()->getName());
// 		$extraction->setDataSourceName($eiType->getDataSourceName());
// 		$extraction->setNestedSetStrategy($eiType->getNestedSetStrategy());
		
// 		$extraction->setEiDefExtraction(self::createEiDefExtraction($eiType->getDefaultEiDef()));
			
// 		if (null !== ($defaultEiMask = $eiType->getEiMaskCollection()->getDefault())) {
// 			$extraction->setDefaultEiMaskId($defaultEiMask->getId());
// 		}
		
// 		foreach ($eiType->getEiMaskCollection() as $eiMask) {
// 			$extraction->addCommonEiMaskExtraction($eiMask->getExtraction());
// 		}
		
// 		foreach ($eiType->getEiEngine()->getEiModificatorCollection() as $eiModificator) {
// 			$extraction->addEiModificatorExtraction($eiModificator->getExtraction());
// 		}
		
// 		return $extraction;
// 	}
	
// 	private static function createEiDefExtraction(EiDef $eiDef) {
// 		$extraction = new EiDefExtraction();
		
// 		$extraction->setLabel($eiDef->getLabel());
// 		$extraction->setPluralLabel($eiDef->getPluralLabel());
// 		$extraction->setIdentityStringPattern($eiDef->getIdentityStringPattern());

// 		$extraction->setFilterGroupData($eiDef->getFilterGroupData());
// 		$extraction->setDefaultSortData($eiDef->getDefaultSortData());
			
// 		$extraction->setPreviewControllerLookupId($eiDef->getPreviewControllerLookupId());
			
// 		foreach ($eiDef->getEiPropCollection()->filterLevel(true) as $eiProp) {
// 			$fieldExtraction = new EiPropExtraction();
// 			$fieldExtraction->setId($eiProp->getId());
// 			$fieldExtraction->setClassName(get_class($eiProp));
// 			$fieldExtraction->setLabel($eiProp->getLabel());
			
// 			$eiFiedConfigurator = $eiProp->createEiConfigurator();
// 			$fieldExtraction->setProps($eiFiedConfigurator->getAttributes()->toArray());
// 			$fieldExtraction->setEntityPropertyName($eiFiedConfigurator->getEntityPropertyName());
// 			$fieldExtraction->setObjectPropertyName($eiFiedConfigurator->getObjectPropertyName());
		
// 			$extraction->addEiPropExtraction($fieldExtraction);
// 		}
			
// 		foreach ($eiDef->getEiCommandCollection()->filterLevel(true) as $command) {
// 			$extraction->addEiCommandExtraction(self::createEiComponentExtraction($command));
// 		}
		
// 		return $extraction;
// 	}
	
	public function toSpecString(): string {
		return 'EiType (id: ' . $this->getId() . ', module: ' . $this->getModuleNamespace() . ')';	
	}
	
// 	private static function createEiComponentExtraction(IndependentEiComponent $configurable) {
// 		$ce = new EiComponentExtraction();
// 		$ce->setId($configurable->getId());
// 		$ce->setClassName(get_class($configurable));
// 		$ce->setProps($configurable->getAttributes()->toArray());
// 		return $ce;
// 	}
}
