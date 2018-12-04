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

use rocket\ei\manage\ManageState;
use n2n\web\http\controller\ControllerContext;
use rocket\ei\EiEngine;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\critmod\filter\FilterCriteriaConstraint;
use rocket\ei\util\Eiu;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\EiCommandPath;

class EiFrameFactory {
	private $eiEngine;
	
	public function __construct(EiEngine $eiEngine) {
		$this->eiEngine = $eiEngine;		
	}
	
	public function create(ControllerContext $controllerContext, ManageState $manageState,  
			?EiFrame $parentEiFrame, EiCommandPath $eiCommandPath) {
		$eiFrame = new EiFrame($this->eiEngine, $manageState);
		$eiFrame->setControllerContext($controllerContext);
		$eiFrame->setParent($parentEiFrame);
		
		$eiMask = $this->eiEngine->getEiMask();
		
		if (null !== ($filterSettingGroup = $eiMask->getFilterSettingGroup())) {
			$filterDefinition = $this->eiEngine->createFramedFilterDefinition($eiFrame);
			
			if ($filterDefinition !== null) {
				$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_FILTER,
						new FilterCriteriaConstraint($filterDefinition->createComparatorConstraint($filterSettingGroup)));
			}
		}
			
		if (null !== ($sortSettingGroup = $eiMask->getSortSettingGroup())) {
			$sortDefinition = $this->eiEngine->createFramedSortDefinition($eiFrame);
			if ($sortDefinition !== null) {
				$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_SORT, 
						$sortDefinition->createCriteriaConstraint($sortSettingGroup));
			}
		}
		
		$manageState->getEiPermissionManager()->applyToEiFrame($eiFrame, $eiCommandPath);
		
		$eiu = new Eiu($eiFrame);
		foreach ($eiMask->getEiModificatorCollection()->toArray() as $eiModificator) {
			$eiModificator->setupEiFrame($eiu);
		}
		
		return $eiFrame;
	}
}
