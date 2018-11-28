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
namespace rocket\impl\ei\component\prop\relation\command;

use rocket\ei\manage\ManageState;
use rocket\core\model\RocketState;
use n2n\web\http\PageNotFoundException;
use rocket\ei\manage\EiObject;
use n2n\web\http\controller\ControllerAdapter;
use rocket\impl\ei\component\prop\relation\model\relation\EiPropRelation;
use rocket\ei\EiController;
use rocket\ei\manage\frame\EiRelation;
use rocket\ei\util\EiuCtrl;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;

class RelationController extends ControllerAdapter {
	private $eiFrame;
	private $manageState;
	private $eiuCtrl;
	private $rocketState;
	private $eiPropRelation;
	
	public function __construct(EiPropRelation $eifieldRelation) {
		$this->eiPropRelation = $eifieldRelation;
	}
	
	public function prepare(ManageState $manageState, RocketState $rocketState, EiuCtrl $eiuCtrl) {
		$this->eiFrame = $manageState->peakEiFrame();
		$this->manageState = $manageState;
		$this->eiuCtrl = $eiuCtrl;
		$this->rocketState = $rocketState;
	}
		
	public function doRelEntry($pid, array $delegateCmds) {
		$eiObject = $this->eiuCtrl->lookupEiObject($pid);
		
		// because RelationCommand gets added always on a supreme EiEngine
		if (!$this->eiPropRelation->getRelationEiProp()->getEiMask()->getEiType()
				->isObjectValid($eiObject->getLiveObject())) {
			throw new PageNotFoundException();
		}
			
		$targetControllerContext = $this->createDelegateContext();
		
		$targetEiFrame = $this->eiPropRelation->createTargetEiFrame($this->manageState, $this->eiFrame, 
				$eiObject, $targetControllerContext);
		
		$this->applyBreadcrumb($eiObject);

		$targetControllerContext->setController(new EiController($targetEiFrame->getContextEiEngine()->getEiMask(), $targetEiFrame));
		
		$this->delegateToControllerContext($targetControllerContext);
	}
	
// 	public function doRelUnknownEntry(array $delegateCmds, EiController $eiTypeController) {
// 		$targetControllerContext = $this->createDelegateContext($eiTypeController);
			
// 		$targetEiFrame = $this->eiPropRelation->createTargetEiFrame($this->manageState, $this->eiFrame,
// 				null, $targetControllerContext);
		
// 		if (null !== ($targetEiProp = $this->eiPropRelation->findTargetEiProp())) {
// 			$targetEiFrame->setEiRelation($targetEiProp->getId(), new EiRelation($this->eiFrame, null));
// 		}
	
// 		$this->applyBreadcrumb();
	
// 		$this->delegate($eiTypeController);
// 	}

	public function doRelNewEntry(string $eiTypeId, array $delegateCmds) {
		$targetControllerContext = $this->createDelegateContext();
		
		$eiu = new Eiu($this->eiFrame);
		$spec = $eiu->context()->getSpec();
		
		if (!$spec->containsEiTypeId($eiTypeId)) {
			throw new PageNotFoundException();
		}
		
		$eiObject = $eiu->frame()->createNewEiObject(false, $spec->getEiTypeById($eiTypeId));
		
		$targetEiFrame = $this->eiPropRelation->createTargetEiFrame($this->manageState, $this->eiFrame,
				$eiObject, $targetControllerContext);
		
		if (null !== ($targetEiProp = $this->eiPropRelation->findTargetEiProp())) {
			$targetEiFrame->setEiRelation(EiPropPath::from($targetEiProp), new EiRelation($this->eiFrame, null));
		}
		
		$this->applyBreadcrumb();
		
		$targetControllerContext->setController(new EiController($targetEiFrame->getContextEiEngine()->getEiMask(), $targetEiFrame));
		$this->delegateToControllerContext($targetControllerContext);
	}
	public function doRel(array $delegateCmds) {
		$targetControllerContext = $this->createDelegateContext();
	
		$targetEiFrame = $this->eiPropRelation->createTargetEiFrame($this->manageState, $this->eiFrame, null, 
				$targetControllerContext);
	
		$this->applyBreadcrumb();
		
		$targetControllerContext->setController(new EiController($targetEiFrame->getContextEiEngine()->getEiMask(), $targetEiFrame));
	
		$this->delegateToControllerContext($targetControllerContext);
	}
	
	private function applyBreadcrumb(EiObject $eiObject = null) {
		if (!$this->eiFrame->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb($this->eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
		}
	
		if ($eiObject !== null && !$this->eiFrame->isDetailDisabled()) {
			$this->rocketState->addBreadcrumb($this->eiFrame->createDetailBreadcrumb($this->getHttpContext(), 
					$eiObject));
		}
	} 
}
