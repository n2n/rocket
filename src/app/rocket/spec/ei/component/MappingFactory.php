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
namespace rocket\spec\ei\component;

use rocket\spec\ei\component\field\EiPropCollection;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\security\PrivilegeConstraint;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use rocket\spec\ei\EiType;
use rocket\spec\ei\security\InaccessibleEntryException;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\mapping\MappingProfile;
use rocket\spec\ei\manage\mapping\EiFieldFork;
use rocket\spec\ei\component\field\FieldEiProp;
use rocket\spec\ei\component\field\EiProp;
use rocket\spec\ei\manage\mapping\EiField;
use rocket\spec\ei\manage\util\model\Eiu;

class MappingFactory {
	private $eiType;
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $fieldCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiPropCollection = $fieldCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}

// 	public function createMappingDefinition() {
// 		$mappingDefinition = new MappingDefinition();
	
// 		foreach ($this->eiPropCollection as $field) {
// // 			if (!($field instanceof FieldEiProp)) continue;
	 
// 			$eiField = $field->getEiField();
// 			if ($eiField === null) continue;
			
// 			ArgUtils::valTypeReturn($eiField, 'rocket\spec\ei\manage\mapping\EiField',
// 					$field, 'createEiField');
				
// 			$mappingDefinition->putEiField($field->getId(), $eiField);
// 		}
	
// 		foreach ($this->modificatorCollection as $modificator) {
// 			$modificator->setupMappingDefinition($mappingDefinition);
// 		}
	
// 		return $mappingDefinition;
// 	}

	/**
	 * @param MappingDefinition $mappingDefinition
	 * @param EiFrame $eiFrame
	 * @param EiObject $eiObject
	 * @param PrivilegeConstraint $privilegeConstraint
	 * @throws InaccessibleEntryException
	 * @return \rocket\spec\ei\manage\mapping\EiEntry
	 */
	public function createEiEntry(EiFrame $eiFrame, EiObject $eiObject, EiEntry $copyFrom = null) {
		$eiEntry = new EiEntry($eiObject);
		$eiu = new Eiu($eiFrame, $eiEntry);
		
		$this->assembleMappingProfile($eiu, $eiEntry, $copyFrom);
		$eiFrame->restrictEiEntry($eiEntry);
	
		foreach ($this->eiModificatorCollection as $constraint) {
			$constraint->setupEiEntry($eiu);
		}
	
		return $eiEntry;
	}
	
	private function assembleMappingProfile(Eiu $eiu, EiEntry $eiMappping, EiEntry $fromEiEntry = null) {
		$eiObject = $eiMappping->getEiObject();
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
				$eiMappping->putEiField($eiPropPath, $eiField);
			}
				
			$eiFieldFork = null;
			if ($fromEiEntry !== null && $eiMappping->containsEiFieldFork($eiPropPath)) {
				$eiFieldFork = $fromEiEntry->getEiFieldFork($eiPropPath)->copyEiFieldFork($eiObject);
			}
			
			if ($eiFieldFork === null) {
				$eiFieldFork = $eiProp->buildEiFieldFork($eiObject, $eiField);
				ArgUtils::valTypeReturn($eiFieldFork, EiFieldFork::class, $eiProp, 'buildEiFieldFork', true);
			}
			
			if ($eiFieldFork !== null) {
				$this->applyEiFieldFork($eiPropPath, $eiFieldFork, $mappingProfile);
			}
		}
	}	
	
	private function applyEiFieldFork(EiPropPath $eiPropPath, EiFieldFork $eiFieldFork, MappingProfile $mappingProfile) {
		$mappingProfile->putEiFieldFork($eiPropPath, $eiFieldFork);
		
		$eiFields = $eiFieldFork->getEiFields();
		ArgUtils::valArrayReturnType($eiFields, 'rocket\spec\ei\manage\mapping\EiField',
				$eiFieldFork, 'getEiFields');
		
		foreach ($eiFields as $id => $eiField) {
			$mappingProfile->putEiField($eiPropPath->pushed($id), $eiField);
		}
		
		$eiFieldForks = $eiFieldFork->getEiFieldForks();
		ArgUtils::valArrayReturnType($eiFields, 'rocket\spec\ei\manage\mapping\EiFieldFork',
				$eiFieldFork, 'getEiFieldForks');
		
		foreach ($eiFieldForks as $id => $eiFieldFork) {
			$this->applyEiFieldFork($eiFieldFork, $eiPropPath->pushed($id), $mappingProfile);
		}
	}
	
	
}
