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
namespace rocket\op\ei\component;

use rocket\op\ei\component\prop\EiPropCollection;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\component\modificator\EiModCollection;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\EiPropPath;

use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\entry\EiFieldMap;
use rocket\op\ei\manage\security\InaccessibleEiEntryException;

class EiEntryFactory {
	private $eiMask;
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiMask $eiMask, EiPropCollection $eiPropCollection, 
			EiModCollection $eiModificatorCollection) {
		$this->eiMask = $eiMask;
		$this->eiPropCollection = $eiPropCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}

// 	public function createMappingDefinition() {
// 		$mappingDefinition = new MappingDefinition();
	
// 		foreach ($this->eiPropCollection as $field) {
// // 			if (!($field instanceof FieldEiProp)) continue;
	 
// 			$eiField = $field->getEiField();
// 			if ($eiField === null) continue;
			
// 			ArgUtils::valTypeReturn($eiField, 'rocket\op\ei\manage\entry\EiField',
// 					$field, 'createEiField');
				
// 			$mappingDefinition->putEiField($field->getId(), $eiField);
// 		}
	
// 		foreach ($this->modificatorCollection as $mod) {
// 			$mod->setupMappingDefinition($mappingDefinition);
// 		}
	
// 		return $mappingDefinition;
// 	}

	/**
	 * @param EiFrame $eiFrame
	 * @param EiObject $eiObject
	 * @throws InaccessibleEiEntryException
	 * @return \rocket\op\ei\manage\entry\EiEntry
	 */
	public function createEiEntry(EiFrame $eiFrame, EiObject $eiObject, ?EiEntry $copyFrom, array $eiEntryConstraints) {
		$eiEntry = new EiEntry($eiObject, $this->eiMask);
		$eiEntry->getConstraintSet()->addAll($eiEntryConstraints);
		
		$eiFieldMap = $eiEntry->getEiFieldMap();
		
		$eiu = new Eiu($eiFrame, $eiEntry, $eiFieldMap);
		
		$this->assembleMappingProfile($eiu, $eiFieldMap, $eiEntry, $copyFrom);
	
		foreach ($this->eiModificatorCollection as $constraint) {
			$constraint->getNature()->setupEiEntry($eiu);
		}
	
		return $eiEntry;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param EiPropPath $eiPropPath
	 * @param object $object
	 * @param EiEntry $copyFrom
	 * @return \rocket\op\ei\manage\entry\EiFieldMap
	 */
	public function createEiFieldMap(EiFrame $eiFrame, EiEntry $eiEntry, EiPropPath $forkEiPropPath, object $object, ?EiEntry $copyFrom) {
		$eiFieldMap = new EiFieldMap($eiEntry, $forkEiPropPath, $object);
		
		$eiu = new Eiu($eiFrame, $eiEntry, $eiFieldMap);
		
		$this->assembleMappingProfile($eiu, $eiFieldMap, $eiEntry, $copyFrom);
		
		foreach ($this->eiModificatorCollection as $constraint) {
			$constraint->getNature()->setupEiEntry($eiu);
		}
		
		return $eiFieldMap;
	}
	
	private function assembleMappingProfile(Eiu $eiu, EiFieldMap $eiFieldMap, EiEntry $eiEntry, ?EiEntry $fromEiEntry = null) {
// 		$eiObject = $eiEntry->getEiObject();
		$forkEiPropPath = $eiFieldMap->getForkEiPropPath();

		foreach ($this->eiPropCollection->getForkedByPath($forkEiPropPath) as $id => $eiProp) {
			$eiPropPath = $forkEiPropPath->ext($id);
			
			$eiField = null;
			if ($fromEiEntry !== null && $fromEiEntry->containsEiField($eiPropPath)) {
				$fromEiField = $fromEiEntry->getEiFieldNature($eiPropPath);
				$eiField = $fromEiField->copyEiField(new Eiu($eiu, $eiProp));
			}
				
			if ($eiField === null) {
				$eiField = $eiProp->getNature()->buildEiField(new Eiu($eiu, $eiProp));
			}
			
			if ($eiField !== null) { 
				$eiFieldMap->put($id, $eiField);
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
			?array $eiPropPaths = null) {
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
		
		foreach ($this->eiPropCollection as $eiProp) {
			$eiPropPath = EiPropPath::from($eiProp);
			
			if (!$fromEiEntry->containsEiField($eiPropPath)|| !$toEiEntry->containsEiField($eiPropPath)) {
				continue;
			}
			
			$fromEiField = $fromEiEntry->getEiFieldNature($eiPropPath);
			$eiFieldCopy = $fromEiField->copyEiField(new Eiu($eiu, $eiProp));
			ArgUtils::valTypeReturn($eiFieldCopy, EiFieldNature::class, $fromEiField, 'copyEiField', true);
			
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
		
		foreach ($eiPropPaths as $eiPropPath) {
			if (!$this->eiPropCollection->containsId($eiPropPath->getFirstId())
					|| !$fromEiEntry->containsEiField($eiPropPath)
					|| !$toEiEntry->containsEiField($eiPropPath)) {
				continue;
			}
			
			$eiProp = $this->eiPropCollection->getByPath($eiPropPath);

			$fromEiField = $fromEiEntry->getEiFieldNature($eiPropPath);
			$copy = $fromEiField->copyEiField(new Eiu($eiu, $eiProp));
			
			if ($copy === null) {
				continue;
			}
			
			$toEiEntry->setValue($eiPropPath, $copy->getValue());
		}
		
	}
	
// 	private function applyEiFieldFork(EiPropPath $eiPropPath, EiFieldFork $eiFieldFork, MappingProfile $mappingProfile) {
// 		$mappingProfile->putEiFieldFork($eiPropPath, $eiFieldFork);
		
// 		$eiFields = $eiFieldFork->getEiFields();
// 		ArgUtils::valArrayReturnType($eiFields, 'rocket\op\ei\manage\entry\EiField',
// 				$eiFieldFork, 'getEiFields');
		
// 		foreach ($eiFields as $id => $eiField) {
// 			$mappingProfile->putEiField($eiPropPath->pushed($id), $eiField);
// 		}
		
// 		$eiFieldForks = $eiFieldFork->getEiFieldForks();
// 		ArgUtils::valArrayReturnType($eiFields, 'rocket\op\ei\manage\entry\EiFieldFork',
// 				$eiFieldFork, 'getEiFieldForks');
		
// 		foreach ($eiFieldForks as $id => $eiFieldFork) {
// 			$this->applyEiFieldFork($eiFieldFork, $eiPropPath->pushed($id), $mappingProfile);
// 		}
// 	}
	
	
}
