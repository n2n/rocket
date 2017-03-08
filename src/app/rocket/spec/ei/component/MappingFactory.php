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

use rocket\spec\ei\component\field\EiFieldCollection;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\security\PrivilegeConstraint;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\security\InaccessibleEntryException;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\mapping\MappingProfile;
use rocket\spec\ei\manage\mapping\MappableFork;
use rocket\spec\ei\component\field\MappableEiField;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\util\model\Eiu;

class MappingFactory {
	private $eiSpec;
	private $eiFieldCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiFieldCollection $fieldCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiFieldCollection = $fieldCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}

// 	public function createMappingDefinition() {
// 		$mappingDefinition = new MappingDefinition();
	
// 		foreach ($this->eiFieldCollection as $field) {
// // 			if (!($field instanceof MappableEiField)) continue;
	 
// 			$mappable = $field->getMappable();
// 			if ($mappable === null) continue;
			
// 			ArgUtils::valTypeReturn($mappable, 'rocket\spec\ei\manage\mapping\Mappable',
// 					$field, 'createMappable');
				
// 			$mappingDefinition->putMappable($field->getId(), $mappable);
// 		}
	
// 		foreach ($this->modificatorCollection as $modificator) {
// 			$modificator->setupMappingDefinition($mappingDefinition);
// 		}
	
// 		return $mappingDefinition;
// 	}

	/**
	 * @param MappingDefinition $mappingDefinition
	 * @param EiFrame $eiFrame
	 * @param EiSelection $eiSelection
	 * @param PrivilegeConstraint $privilegeConstraint
	 * @throws InaccessibleEntryException
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 */
	public function createEiMapping(EiFrame $eiFrame, EiSelection $eiSelection, EiMapping $copyFrom = null) {
		$eiMapping = new EiMapping($eiSelection);
		$eiu = new Eiu($eiFrame, $eiMapping);
		
		$this->assembleMappingProfile($eiu, $eiMapping, $copyFrom);
		$eiFrame->restrictEiMapping($eiMapping);
	
		foreach ($this->eiModificatorCollection as $constraint) {
			$constraint->setupEiMapping($eiu);
		}
	
		return $eiMapping;
	}
	
	private function assembleMappingProfile(Eiu $eiu, EiMapping $eiMappping, EiMapping $fromEiMapping = null) {
		$eiSelection = $eiMappping->getEiSelection();
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof MappableEiField)) continue;
						
			$eiFieldPath = new EiFieldPath(array($id));
			
			$mappable = null;
			if ($fromEiMapping !== null && $fromEiMapping->containsMappable($eiFieldPath)) {
				$fromMappable = $fromEiMapping->getMappable($eiFieldPath);
				$mappable = $fromMappable->copyMappable(new Eiu($eiu, $eiField));
				ArgUtils::valTypeReturn($mappable, Mappable::class, $fromMappable, 'copyMappable', true);
			}
				
			if ($mappable === null) {
				$mappable = $eiField->buildMappable(new Eiu($eiu, $eiField));
				ArgUtils::valTypeReturn($mappable, Mappable::class, $eiField, 'buildMappable', true);
			}

			if ($mappable !== null) {
				$eiMappping->putMappable($eiFieldPath, $mappable);
			}
				
			$mappableFork = null;
			if ($fromEiMapping !== null && $eiMappping->containsMappableFork($eiFieldPath)) {
				$mappableFork = $fromEiMapping->getMappableFork($eiFieldPath)->copyMappableFork($eiSelection);
			}
			
			if ($mappableFork === null) {
				$mappableFork = $eiField->buildMappableFork($eiSelection, $mappable);
				ArgUtils::valTypeReturn($mappableFork, MappableFork::class, $eiField, 'buildMappableFork', true);
			}
			
			if ($mappableFork !== null) {
				$this->applyMappableFork($eiFieldPath, $mappableFork, $mappingProfile);
			}
		}
	}	
	
	private function applyMappableFork(EiFieldPath $eiFieldPath, MappableFork $mappableFork, MappingProfile $mappingProfile) {
		$mappingProfile->putMappableFork($eiFieldPath, $mappableFork);
		
		$mappables = $mappableFork->getMappables();
		ArgUtils::valArrayReturnType($mappables, 'rocket\spec\ei\manage\mapping\Mappable',
				$mappableFork, 'getMappables');
		
		foreach ($mappables as $id => $mappable) {
			$mappingProfile->putMappable($eiFieldPath->pushed($id), $mappable);
		}
		
		$mappableForks = $mappableFork->getMappableForks();
		ArgUtils::valArrayReturnType($mappables, 'rocket\spec\ei\manage\mapping\MappableFork',
				$mappableFork, 'getMappableForks');
		
		foreach ($mappableForks as $id => $mappableFork) {
			$this->applyMappableFork($mappableFork, $eiFieldPath->pushed($id), $mappingProfile);
		}
	}
	
	
}
