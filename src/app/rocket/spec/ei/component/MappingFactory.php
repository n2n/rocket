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
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\security\InaccessibleEntryException;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\mapping\MappingProfile;
use rocket\spec\ei\manage\mapping\MappableFork;
use rocket\spec\ei\component\field\MappableEiProp;
use rocket\spec\ei\component\field\EiProp;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\util\model\Eiu;

class MappingFactory {
	private $eiSpec;
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $fieldCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiPropCollection = $fieldCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}

// 	public function createMappingDefinition() {
// 		$mappingDefinition = new MappingDefinition();
	
// 		foreach ($this->eiPropCollection as $field) {
// // 			if (!($field instanceof MappableEiProp)) continue;
	 
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
	 * @param EiObject $eiObject
	 * @param PrivilegeConstraint $privilegeConstraint
	 * @throws InaccessibleEntryException
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 */
	public function createEiMapping(EiFrame $eiFrame, EiObject $eiObject, EiMapping $copyFrom = null) {
		$eiMapping = new EiMapping($eiObject);
		$eiu = new Eiu($eiFrame, $eiMapping);
		
		$this->assembleMappingProfile($eiu, $eiMapping, $copyFrom);
		$eiFrame->restrictEiMapping($eiMapping);
	
		foreach ($this->eiModificatorCollection as $constraint) {
			$constraint->setupEiMapping($eiu);
		}
	
		return $eiMapping;
	}
	
	private function assembleMappingProfile(Eiu $eiu, EiMapping $eiMappping, EiMapping $fromEiMapping = null) {
		$eiObject = $eiMappping->getEiObject();
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof MappableEiProp)) continue;
						
			$eiPropPath = new EiPropPath(array($id));
			
			$mappable = null;
			if ($fromEiMapping !== null && $fromEiMapping->containsMappable($eiPropPath)) {
				$fromMappable = $fromEiMapping->getMappable($eiPropPath);
				$mappable = $fromMappable->copyMappable(new Eiu($eiu, $eiProp));
				ArgUtils::valTypeReturn($mappable, Mappable::class, $fromMappable, 'copyMappable', true);
			}
				
			if ($mappable === null) {
				$mappable = $eiProp->buildMappable(new Eiu($eiu, $eiProp));
				ArgUtils::valTypeReturn($mappable, Mappable::class, $eiProp, 'buildMappable', true);
			}

			if ($mappable !== null) {
				$eiMappping->putMappable($eiPropPath, $mappable);
			}
				
			$mappableFork = null;
			if ($fromEiMapping !== null && $eiMappping->containsMappableFork($eiPropPath)) {
				$mappableFork = $fromEiMapping->getMappableFork($eiPropPath)->copyMappableFork($eiObject);
			}
			
			if ($mappableFork === null) {
				$mappableFork = $eiProp->buildMappableFork($eiObject, $mappable);
				ArgUtils::valTypeReturn($mappableFork, MappableFork::class, $eiProp, 'buildMappableFork', true);
			}
			
			if ($mappableFork !== null) {
				$this->applyMappableFork($eiPropPath, $mappableFork, $mappingProfile);
			}
		}
	}	
	
	private function applyMappableFork(EiPropPath $eiPropPath, MappableFork $mappableFork, MappingProfile $mappingProfile) {
		$mappingProfile->putMappableFork($eiPropPath, $mappableFork);
		
		$mappables = $mappableFork->getMappables();
		ArgUtils::valArrayReturnType($mappables, 'rocket\spec\ei\manage\mapping\Mappable',
				$mappableFork, 'getMappables');
		
		foreach ($mappables as $id => $mappable) {
			$mappingProfile->putMappable($eiPropPath->pushed($id), $mappable);
		}
		
		$mappableForks = $mappableFork->getMappableForks();
		ArgUtils::valArrayReturnType($mappables, 'rocket\spec\ei\manage\mapping\MappableFork',
				$mappableFork, 'getMappableForks');
		
		foreach ($mappableForks as $id => $mappableFork) {
			$this->applyMappableFork($mappableFork, $eiPropPath->pushed($id), $mappingProfile);
		}
	}
	
	
}
