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

use rocket\spec\ei\EiSpec;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\EiDef;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\spec\config\UnknownMaskException;

class EiSpecExtraction extends SpecExtraction {
	private $entityClassName;
	private $eiDefExtraction;
	private $dataSourceName;
	private $nestedSetStrategy;
	private $defaultEiMaskId;
	private $commonEiMaskExtractions = array();

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
	
// 	public function createScript(SpecManager $scriptManager) {
// 		$factory = new EiSpecFactory($scriptManager->getEntityModelManager(), $scriptManager->getEiSpecSetupQueue());
// 		return $factory->create($this);
// 	}
	
	public static function createFromEiSpec(EiSpec $eiSpec) {
		$extraction = new EiSpecExtraction($eiSpec->getId(), $eiSpec->getModuleNamespace());
		$extraction->setEntityClassName($eiSpec->getEntityModel()->getClass()->getName());
		$extraction->setDataSourceName($eiSpec->getDataSourceName());
		$extraction->setNestedSetStrategy($eiSpec->getNestedSetStrategy());
		
		$extraction->setEiDefExtraction(self::createEiDefExtraction($eiSpec->getDefaultEiDef()));
			
		if (null !== ($defaultEiMask = $eiSpec->getEiMaskCollection()->getDefault())) {
			$extraction->setDefaultEiMaskId($defaultEiMask->getId());
		}
		
		foreach ($eiSpec->getEiMaskCollection() as $eiMask) {
			$extraction->addCommonEiMaskExtraction($eiMask->getExtraction());
		}
		
		return $extraction;
	}
	
	private static function createEiDefExtraction(EiDef $eiDef) {
		$extraction = new EiDefExtraction();
		
		$extraction->setLabel($eiDef->getLabel());
		$extraction->setPluralLabel($eiDef->getPluralLabel());
		$extraction->setIdentityStringPattern($eiDef->getIdentityStringPattern());

		$extraction->setFilterGroupData($eiDef->getFilterGroupData());
		$extraction->setDefaultSortData($eiDef->getDefaultSortData());
			
		$extraction->setPreviewControllerLookupId($eiDef->getPreviewControllerLookupId());
			
		foreach ($eiDef->getEiFieldCollection()->filterLevel(true) as $eiField) {
			$fieldExtraction = new EiFieldExtraction();
			$fieldExtraction->setId($eiField->getId());
			$fieldExtraction->setClassName(get_class($eiField));
			$fieldExtraction->setLabel($eiField->getLabel());
			
			$eiFiedConfigurator = $eiField->createEiConfigurator();
			$fieldExtraction->setProps($eiFiedConfigurator->getAttributes()->toArray());
			$fieldExtraction->setEntityPropertyName($eiFiedConfigurator->getEntityPropertyName());
			$fieldExtraction->setObjectPropertyName($eiFiedConfigurator->getObjectPropertyName());
		
			$extraction->addEiFieldExtraction($fieldExtraction);
		}
			
		foreach ($eiDef->getEiCommandCollection()->filterLevel(true) as $command) {
			$extraction->addEiCommandExtraction(self::createEiComponentExtraction($command));
		}
			
		foreach ($eiDef->getEiModificatorCollection()->filterLevel(true) as $constraint) {
			$extraction->addEiModificatorExtraction(self::createEiComponentExtraction($constraint));
		}
		
		return $extraction;
	}
	
	public function toSpecString(): string {
		return 'EiSpec (id: ' . $this->getId() . ', module: ' . $this->getModuleNamespace() . ')';	
	}
	
// 	private static function createEiComponentExtraction(IndependentEiComponent $configurable) {
// 		$ce = new EiComponentExtraction();
// 		$ce->setId($configurable->getId());
// 		$ce->setClassName(get_class($configurable));
// 		$ce->setProps($configurable->getAttributes()->toArray());
// 		return $ce;
// 	}
}
