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
namespace rocket\ei\component;

use rocket\ei\component\prop\EiPropCollection;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\component\modificator\EiModificatorCollection;
use rocket\ei\security\InaccessibleEntryException;
use rocket\ei\manage\mapping\EiEntry;
use rocket\ei\EiPropPath;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\manage\mapping\EiField;
use rocket\ei\util\Eiu;
use rocket\ei\mask\EiMask;

class EiEntryFactory {
	private $eiMask;
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiMask $eiMask, EiPropCollection $fieldCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiMask = $eiMask;
		$this->eiPropCollection = $fieldCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}

// 	public function createMappingDefinition() {
// 		$mappingDefinition = new MappingDefinition();
	
// 		foreach ($this->eiPropCollection as $field) {
// // 			if (!($field instanceof FieldEiProp)) continue;
	 
// 			$eiField = $field->getEiField();
// 			if ($eiField === null) continue;
			
// 			ArgUtils::valTypeReturn($eiField, 'rocket\ei\manage\mapping\EiField',
// 					$field, 'createEiField');
				
// 			$mappingDefinition->putEiField($field->getId(), $eiField);
// 		}
	
// 		foreach ($this->modificatorCollection as $modificator) {
// 			$modificator->setupMappingDefinition($mappingDefinition);
// 		}
	
// 		return $mappingDefinition;
// 	}

	/**
	 * @param EiFrame $eiFrame
	 * @param EiObject $eiObject
	 * @throws InaccessibleEntryException
	 * @return \rocket\ei\manage\mapping\EiEntry
	 */
	public function createEiEntry(EiFrame $eiFrame, EiObject $eiObject, ?EiEntry $copyFrom, array $eiEntryConstraints) {
		$eiEntry = new EiEntry($eiObject, $this->eiMask);
		$eiEntry->getConstraintSet()->addAll($eiEntryConstraints);
		
		$eiu = new Eiu($eiFrame, $eiEntry);
		
		$this->assembleMappingProfile($eiu, $eiEntry, $copyFrom);
	
		foreach ($this->eiModificatorCollection as $constraint) {
			$constraint->setupEiEntry($eiu);
		}
	
		return $eiEntry;
	}
	
	private function assembleMappingProfile(Eiu $eiu, EiEntry $eiEntry, EiEntry $fromEiEntry = null) {
		$eiObject = $eiEntry->getEiObject();
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof FieldEiProp)) continue;
						
			$eiPropPath = new EiPropPath(array($id));
			
			$eiField = null;
			if ($fromEiEntry !== null && $fromEiEntry->containsEiField($eiPropPath)) {
				$fromEiField = $fromEiEntry->getEiField($eiPropPath);
				$eiField = $fromEiField->copyEiField(new Eiu($eiu, $eiProp));
				ArgUtils::valTypeReturn($eiField, EiField::class, $fromEiField, 'copyEiField', true);
			}
				
			if ($eiField === null) {
				$eiField = $eiProp->buildEiField(new Eiu($eiu, $eiProp));
				ArgUtils::valTypeReturn($eiField, EiField::class, $eiProp, 'buildEiField', true);
			}

			if ($eiField !== null) {
				$eiEntry->putEiField($eiPropPath, $eiField);
			}
				
// 			$eiFieldFork = null;
// 			if ($fromEiEntry !== null && $eiEntry->containsEiFieldFork($eiPropPath)) {
// 				$eiFieldFork = $fromEiEntry->getEiFieldFork($eiPropPath)->copyEiFieldFork($eiObject);
// 			}
			
// 			if ($eiFieldFork === null) {
// 				$eiFieldFork = $eiProp->buildEiFieldFork($eiObject, $eiField);
// 				ArgUtils::valTypeReturn($eiFieldFork, EiFieldFork::class, $eiProp, 'buildEiFieldFork', true);
// 			}
			
// 			if ($eiFieldFork !== null) {
// 				$this->applyEiFieldFork($eiPropPath, $eiFieldFork, $mappingProfile);
// 			}
		}
	}
	
	public function copyValues(EiFrame $eiFrame, EiEntry $fromEiEntry, EiEntry $toEiEntry,
			array $eiPropPaths = null) {
		if ($eiPropPaths === null) {
			$this->copyAllValues($eiFrame, $fromEiEntry, $toEiEntry);
		} else {
			$this->copySpecificValues($eiFrame, $fromEiEntry, $toEiEntry, $eiPropPaths);
		}
	}
	
	/**
	 *
	 * @param Eiu $eiu
	 * @param EiEntry $fromEiEntry
	 * @param EiEntry $toEiEntry
	 * @param EiPropPath[] $eiPropPaths
	 */
	private function copyAllValues(EiFrame $eiFrame, EiEntry $fromEiEntry, EiEntry $toEiEntry) {
		$eiu = new Eiu($eiFrame, $toEiEntry);
		
		foreach ($this->eiPropCollection as $id => $eiProp) {
			$eiPropPath = EiPropPath::from($eiProp);
			
			if (!$fromEiEntry->containsEiField($eiPropPath)|| !$toEiEntry->containsEiField($eiPropPath)) {
				continue;
			}
			
			$fromEiField = $fromEiEntry->getEiField($eiPropPath);
			$eiFieldCopy = $fromEiField->copyEiField(new Eiu($eiu, $eiProp));
			ArgUtils::valTypeReturn($eiFieldCopy, EiField::class, $fromEiField, 'copyEiField', true);
			
			if ($eiFieldCopy === null) {
				continue;
			}
			
			$toEiEntry->setValue($eiPropPath, $eiFieldCopy->getValue());
		}
	}
	
	/**
	 * 
	 * @param Eiu $eiu
	 * @param EiEntry $fromEiEntry
	 * @param EiEntry $toEiEntry
	 * @param EiPropPath[] $eiPropPaths
	 */
	private function copySpecificValues(EiFrame $eiFrame, EiEntry $fromEiEntry, EiEntry $toEiEntry, array $eiPropPaths) {
		$eiu = new Eiu($eiFrame, $toEiEntry);
		
		foreach ($eiPropPaths as $id => $eiPropPath) {
			if (!$this->eiPropCollection->containsId($eiPropPath->getFirstId())
					|| !$fromEiEntry->containsEiField($eiPropPath)
					|| !$toEiEntry->containsEiField($eiPropPath)) {
				continue;
			}
			
			$eiProp = $this->eiPropCollection->getById($eiPropPath->getFirstId());
			if (!($eiProp instanceof FieldEiProp)) continue;
			
			$fromEiField = $fromEiEntry->getEiField($eiPropPath);
			$copy = $fromEiField->copyEiField(new Eiu($eiu, $eiProp));
			ArgUtils::valTypeReturn($copy, EiField::class, $fromEiField, 'copyEiField', true);
			
			if ($copy === null) {
				continue;
			}
			
			$toEiEntry->setValue($eiPropPath, $copy->getValue());
		}
		
	}
	
// 	private function applyEiFieldFork(EiPropPath $eiPropPath, EiFieldFork $eiFieldFork, MappingProfile $mappingProfile) {
// 		$mappingProfile->putEiFieldFork($eiPropPath, $eiFieldFork);
		
// 		$eiFields = $eiFieldFork->getEiFields();
// 		ArgUtils::valArrayReturnType($eiFields, 'rocket\ei\manage\mapping\EiField',
// 				$eiFieldFork, 'getEiFields');
		
// 		foreach ($eiFields as $id => $eiField) {
// 			$mappingProfile->putEiField($eiPropPath->pushed($id), $eiField);
// 		}
		
// 		$eiFieldForks = $eiFieldFork->getEiFieldForks();
// 		ArgUtils::valArrayReturnType($eiFields, 'rocket\ei\manage\mapping\EiFieldFork',
// 				$eiFieldFork, 'getEiFieldForks');
		
// 		foreach ($eiFieldForks as $id => $eiFieldFork) {
// 			$this->applyEiFieldFork($eiFieldFork, $eiPropPath->pushed($id), $mappingProfile);
// 		}
// 	}
	
	
}
