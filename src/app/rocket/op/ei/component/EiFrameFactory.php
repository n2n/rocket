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

use rocket\op\ei\manage\ManageState;
use n2n\web\http\controller\ControllerContext;
use rocket\op\ei\EiEngine;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\critmod\filter\FilterCriteriaConstraint;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\frame\Boundry;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\manage\security\InaccessibleEiCmdPathException;
use rocket\op\ei\EiException;
use rocket\op\ei\EiPropPath;
use n2n\util\type\TypeUtils;
use rocket\op\ei\manage\frame\EiForkLink;
use rocket\op\ei\manage\EiLaunch;

class EiFrameFactory {
	private $eiEngine;
	
	public function __construct(EiEngine $eiEngine) {
		$this->eiEngine = $eiEngine;		
	}

	/**
	 * @param EiLaunch $eiLaunch
	 * @param EiForkLink|null $eiForkLink
	 * @return EiFrame
	 */
	public function create(EiLaunch $eiLaunch, EiForkLink $eiForkLink = null): EiFrame {
		$eiFrame = new EiFrame($this->eiEngine, $eiLaunch, $eiForkLink);

		$eiMask = $this->eiEngine->getEiMask();

		if (null !== ($filterSettingGroup = $eiMask->getFilterSettingGroup())) {
			$filterDefinition = $eiFrame->getFilterDefinition(); // $this->eiEngine->createFramedFilterDefinition($eiFrame);
			$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_FILTER,
					new FilterCriteriaConstraint($filterDefinition->createComparatorConstraint($filterSettingGroup)));
		}

		if (null !== ($sortSettingGroup = $eiMask->getSortSettingGroup())) {
			$sortDefinition = $eiFrame->getSortDefinition(); //$this->eiEngine->createFramedSortDefinition($eiFrame);
			$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_SORT,
					$sortDefinition->createCriteriaConstraint($sortSettingGroup));
		}

		return $eiFrame;
	}
	

	

	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiForkLink $eiForkLink
	 * @throws EiException
	 * @return \rocket\op\ei\manage\frame\EiFrame
	 */
	public function createForked(EiPropPath $eiPropPath, EiForkLink $eiForkLink) {
		$eiProp = $this->eiEngine->getEiMask()->getEiPropCollection()->getByPath($eiPropPath);

		$parentEiFrame = $eiForkLink->getParent();
		$eiu = new Eiu($parentEiFrame, $eiForkLink->getParentEiObject(), $eiPropPath);
		$forkedEiFrame = $eiProp->getNature()->createForkedEiFrame($eiu, $eiForkLink);
		
		if ($forkedEiFrame->hasEiExecution()) {
			throw new EiException(TypeUtils::prettyMethName(get_class($eiProp), 'createForkedEiFrame')
					. ' must return an EiFrame which is not yet executed.');
		}
		
		$forkedEiFrame->setEiForkLink($eiForkLink);
		
		$forkedEiFrame->setBaseUrl($parentEiFrame->getForkUrl(null, $eiPropPath,
				$eiForkLink->getMode(), $eiForkLink->getParentEiObject()));
		
		$this->setupEiFrame($forkedEiFrame);
		
		return $forkedEiFrame;
	}
	
	
}

// class ForkBaseLinkProvider implements EiFrameListener {
// 	private $parentEiFrame;
// 	private $forkedEiFrame;
// 	private $eiPropPath;
// 	private $eiForkLink;
	
// 	function __construct(EiFrame $parentEiFrame, EiFrame $forkedEiFrame, EiPropPath $eiPropPath, 
// 			EiForkLink $eiForkLink) {
// 		$this->parentEiFrame = $parentEiFrame;
// 		$this->forkedEiFrame = $forkedEiFrame;
// 		$this->eiPropPath = $eiPropPath;
// 		$this->eiForkLink = $eiForkLink;
// 	}
	
// 	function onNewEiEntry(EiEntry $eiEntry) {
// 	}
	
// 	function whenExecuted(EiExecution $eiExecution) {
// 		if ($this->forkedEiFrame->hasBaseUrl()) {
// 			return;
// 		}
		
// 		$eiCmdPath = EiCmdPath::from($eiExecution->getEiCommand());
		
// 		$this->forkedEiFrame->setBaseUrl($this->parentEiFrame->getForkUrl($eiCmdPath, $this->eiPropPath, 
// 				$this->eiForkLink->getMode(), $this->eiForkLink->getParentEiObject()));
// 	}
// }
