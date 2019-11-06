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
namespace rocket\impl\ei\component\prop\numeric\conf;

use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\impl\ei\component\prop\numeric\component\OrderEiCommand;
use rocket\impl\ei\component\prop\numeric\component\OrderEiModificator;
use rocket\impl\ei\component\prop\adapter\config\EntityPropertyConfigurable;
use n2n\persistence\meta\structure\Column;
use rocket\impl\ei\component\prop\adapter\config\EiPropConfiguratorAdaption;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\numeric\OrderEiProp;

class OrderConfig implements EiPropConfiguratorAdaption {

	const COMMON_ORDER_INDEX_PROP_NAME = 'orderIndex';
	const OPTION_REFERENCE_FIELD_KEY = 'referenceField';
	
	private $orderEiProp;
	
	public function __construct(OrderEiProp $orderEiProp) {
		$this->orderEiProp = $orderEiProp;
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE == $level) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
		
		if ($propertyAssignation->hasEntityProperty()
				&& $propertyAssignation->getEntityProperty()->getName() == self::COMMON_ORDER_INDEX_PROP_NAME) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
// 		$eiDef = $setupProcess->getEiDef();
		
// 		if ($this->dataSet->contains(self::OPTION_REFERENCE_FIELD_KEY)) {
// 			$this->eiComponent->setReferenceField($eiDef->getEiPropCollection()->getById(
// 					$this->dataSet->get(self::OPTION_REFERENCE_FIELD_KEY)));
// 		}
		
		$orderEiCommand = new OrderEiCommand();
		$orderEiCommand->setOrderEiProp($this->orderEiProp);
		
		$eiuMask = $eiu->mask();
		$eiuMask->addEiCommand($orderEiCommand);
		$eiuMask->addEiModificator(new OrderEiModificator($this->orderEiProp));
		
// 		if (count($eiDef->getDefaultSortSettingGroup()) === 0) {
// 		    $eiDef->setDefaultSortSettingGroup(array($this->eiComponent->getEntityProperty()->getName() => Criteria::ORDER_DIRECTION_ASC));
// 		}
	}
	
	public function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$this->dataSet->set(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, false);
		$this->dataSet->set(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, false);
		$this->dataSet->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);
	}
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		return $magDispatchable;
		
		$magCollection = $magDispatchable->getMagCollection();
		$magCollection->addMag(new EnumMag(self::OPTION_REFERENCE_FIELD_KEY, 
				'Reference Field', $this->generateReferenceEnumMags()));
		return $magDispatchable;
	}

	private function generateReferenceEnumMags() {
		$referenceFields = array();
		foreach ($this->eiComponent->getEiType()->getEiPropCollection()->combineAll() as $eiProp) {
			if (!($eiProp instanceof EntityPropertyConfigurable)) continue;
			$referenceFields[$eiProp->getId()] = $eiProp->getLabelCode();
		}
		return $referenceFields;
	}
	
	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
	}

}
