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

use rocket\spec\ei\component\field\SortableEiField;
use rocket\spec\ei\manage\critmod\SortModel;
use rocket\spec\ei\component\field\EiFieldCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\command\EiCommandCollection;
use rocket\spec\ei\component\field\PrivilegedEiField;
use rocket\spec\security\PrivilegeDefinition;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use rocket\spec\ei\EiCommandPath;
use n2n\reflection\ArgUtils;
use rocket\spec\security\EiFieldPrivilege;

class SecurityFactory {
	private $eiFieldCollection;
	private $eiCommandCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiFieldCollection $eiFieldCollection, EiCommandCollection $eiCommandCollection ,
			EiModificatorCollection $eiModificatorCollection) {
		$this->eiFieldCollection = $eiFieldCollection;
		$this->eiCommandCollection = $eiCommandCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
// 	public static function createFilterModel(EiSpec $eiSpec, N2nContext $n2nContext) {
// 		return self::createFilterModelInstance($eiSpec, $n2nContext);
// 	}
	
// 	public static function createFilterModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createFilterModelInstance($eiFrame->getContextEiMask()->getEiEngine()->getEiSpec(), 
// 				$eiFrame->getN2nContext(), $eiFrame);
// 	}
		
	public function createPrivilegedDefinition(N2nContext $n2nContext): PrivilegeDefinition {
		$privilegeDefinition = new PrivilegeDefinition();
		foreach ($this->eiCommandCollection->toArray(false) as $eiCommand) {
			if (!($eiCommand instanceof PrivilegedEiCommand)) continue;
			
			$privilegeDefinition->putEiCommandPrivilege(EiCommandPath::from($eiCommand), $eiCommand->createEiCommandPrivilege($n2nContext));
		}	
		
		foreach ($this->eiFieldCollection->toArray(false) as $eiField) {
			if (!($eiField instanceof PrivilegedEiField)) continue;
				
			$eiFieldPrivilege = $eiField->createEiFieldPrivilege($n2nContext);
			ArgUtils::valTypeReturn($eiFieldPrivilege, EiFieldPrivilege::class, $eiField, 'buildEiFieldPrivilege');
			
			if ($eiFieldPrivilege !== null) {
				$privilegeDefinition->putEiFieldPrivilege(EiFieldPath::from($eiField), $eiFieldPrivilege);
			}
		}
		
		return $privilegeDefinition;
	}
	
// 	public static function createSortModel(EiSpec $eiSpec, N2nContext $n2nContext) {
// 		return self::createSortModelInstance($eiSpec, $n2nContext);
// 	}
	
// 	public static function createSortModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createSortModelInstance($eiFrame->getContextEiMask()->getEiEngine()->getEiSpec(), $eiFrame->getN2nContext());
// 	}
	
	public static function createSortModel() {
		$sortModel = new SortModel();
		foreach ($this->eiFieldCollection as $id => $eiField) {
			if (!($eiField instanceof SortableEiField)) continue;
			
			if (null !== ($sortItem = $eiField->getSortItem())) {
				$sortModel->putSortItem($id, $eiField->getSortItem());
			}
			
			if (null !== ($sortItemFork = $eiField->getSortItemFork())) {
				$sortModel->putSortItemFork($id, $eiField->getSortItemFork());
			}
		}
		return $sortModel;
	}
		
// 	public static function createQuickSearchableModel(EiFrame $eiFrame) {
// 		$quickSerachModel = new QuickSearchModel();
// 		foreach ($eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->getEiFieldCollection() as $field) {
// 			if ($field instanceof QuickSearchableEiField) {
// 				$quickSerachModel->addQuickSearchable($field);
// 			}
// 		}
// 		return $quickSerachModel;
// 	}
}
