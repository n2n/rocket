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

use rocket\spec\ei\component\prop\EiPropCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\component\command\EiCommandCollection;
use rocket\spec\ei\component\prop\PrivilegedEiProp;
use rocket\spec\security\PrivilegeDefinition;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use rocket\spec\ei\EiCommandPath;
use n2n\reflection\ArgUtils;
use rocket\spec\security\EiPropPrivilege;

class SecurityFactory {
	private $eiPropCollection;
	private $eiCommandCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $eiPropCollection, EiCommandCollection $eiCommandCollection ,
			EiModificatorCollection $eiModificatorCollection) {
		$this->eiPropCollection = $eiPropCollection;
		$this->eiCommandCollection = $eiCommandCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
// 	public static function createFilterModel(EiType $eiType, N2nContext $n2nContext) {
// 		return self::createFilterModelInstance($eiType, $n2nContext);
// 	}
	
// 	public static function createFilterModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createFilterModelInstance($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), 
// 				$eiFrame->getN2nContext(), $eiFrame);
// 	}
		
	public function createPrivilegedDefinition(N2nContext $n2nContext): PrivilegeDefinition {
		$privilegeDefinition = new PrivilegeDefinition();
		foreach ($this->eiCommandCollection->toArray(false) as $eiCommand) {
			if (!($eiCommand instanceof PrivilegedEiCommand)) continue;
			
			$privilegeDefinition->putEiCommandPrivilege(EiCommandPath::from($eiCommand), $eiCommand->createEiCommandPrivilege($n2nContext));
		}	
		
		foreach ($this->eiPropCollection->toArray(false) as $eiProp) {
			if (!($eiProp instanceof PrivilegedEiProp)) continue;
				
			$eiPropPrivilege = $eiProp->createEiPropPrivilege($n2nContext);
			ArgUtils::valTypeReturn($eiPropPrivilege, EiPropPrivilege::class, $eiProp, 'buildEiPropPrivilege');
			
			if ($eiPropPrivilege !== null) {
				$privilegeDefinition->putEiPropPrivilege(EiPropPath::from($eiProp), $eiPropPrivilege);
			}
		}
		
		return $privilegeDefinition;
	}
	
// 	public static function createSortModel(EiType $eiType, N2nContext $n2nContext) {
// 		return self::createSortModelInstance($eiType, $n2nContext);
// 	}
	
// 	public static function createSortModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createSortModelInstance($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $eiFrame->getN2nContext());
// 	}
	
// 	public static function createSortModel() {
// 		$sortModel = new SortModel();
// 		foreach ($this->eiPropCollection as $id => $eiProp) {
// 			if (!($eiProp instanceof SortableEiProp)) continue;
			
// 			if (null !== ($sortItem = $eiProp->getSortItem())) {
// 				$sortModel->putSortItem($id, $eiProp->getSortItem());
// 			}
			
// 			if (null !== ($sortItemFork = $eiProp->getSortItemFork())) {
// 				$sortModel->putSortItemFork($id, $eiProp->getSortItemFork());
// 			}
// 		}
// 		return $sortModel;
// 	}
		
// 	public static function createQuickSearchableModel(EiFrame $eiFrame) {
// 		$quickSerachModel = new QuickSearchModel();
// 		foreach ($eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getEiPropCollection() as $field) {
// 			if ($field instanceof QuickSearchableEiProp) {
// 				$quickSerachModel->addQuickSearchable($field);
// 			}
// 		}
// 		return $quickSerachModel;
// 	}
}
