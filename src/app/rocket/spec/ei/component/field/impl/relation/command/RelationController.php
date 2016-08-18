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
namespace rocket\spec\ei\component\field\impl\relation\command;

use rocket\spec\ei\manage\ManageState;
use rocket\core\model\RocketState;
use n2n\http\PageNotFoundException;
use rocket\spec\ei\manage\EiSelection;
use n2n\http\controller\ControllerAdapter;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use rocket\spec\ei\component\command\impl\common\controller\ControllingUtils;
use rocket\spec\ei\EiSpecController;
use rocket\spec\ei\manage\EiRelation;
use rocket\spec\ei\component\command\impl\common\controller\EiCtrlUtils;

class RelationController extends ControllerAdapter {
	private $eiState;
	private $manageState;
	private $eiCtrlUtils;
	private $rocketState;
	private $eiFieldRelation;
	
	public function __construct(EiFieldRelation $eifieldRelation) {
		$this->eiFieldRelation = $eifieldRelation;
	}
	
	public function prepare(ManageState $manageState, RocketState $rocketState) {
		$this->eiState = $manageState->peakEiState();
		$this->manageState = $manageState;
		$this->eiCtrlUtils = EiCtrlUtils::from($this->getHttpContext());
		$this->rocketState = $rocketState;
	}
		
	public function doRelEntry($idRep, array $delegateCmds, EiSpecController $eiSpecController) {
		$eiSelection = $this->eiCtrlUtils->lookupEiSelection($idRep);
		
		// because RelationCommand gets added always on a supreme EiThing
		if (!$this->eiFieldRelation->getRelationEiField()->getEiEngine()->getEiSpec()
				->isObjectValid($eiSelection->getLiveObject())) {
			throw new PageNotFoundException();
		}
			
		$targetControllerContext = $this->createDelegateContext($eiSpecController);
		
		$this->eiFieldRelation->createTargetEiState($this->manageState, $this->eiState, 
				$eiSelection, $targetControllerContext);
		
		$this->applyBreadcrumb($eiSelection);

		$this->delegate($eiSpecController);
	}
	
	public function doRelUnknownEntry(array $delegateCmds, EiSpecController $eiSpecController) {
		$targetControllerContext = $this->createDelegateContext($eiSpecController);
			
		$targetEiState = $this->eiFieldRelation->createTargetEiState($this->manageState, $this->eiState,
				null, $targetControllerContext);
		
		if (null !== ($targetEiField = $this->eiFieldRelation->findTargetEiField())) {
			$targetEiState->setEiRelation($targetEiField->getId(), new EiRelation($this->eiState, null));
		}
	
		$this->applyBreadcrumb();
	
		$this->delegate($eiSpecController);
	}
	
	public function doRel(array $delegateCmds, EiSpecController $eiSpecController) {
		$targetControllerContext = $this->createDelegateContext($eiSpecController);
	
		$targetEiState = $this->eiFieldRelation->createTargetEiState($this->manageState, $this->eiState, null, 
				$targetControllerContext);
	
		$this->applyBreadcrumb();
	
		$this->delegate($eiSpecController);
	}
	
	private function applyBreadcrumb(EiSelection $eiSelection = null) {
		if (!$this->eiState->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb($this->eiState->createOverviewBreadcrumb($this->getHttpContext()));
		}
	
		if ($eiSelection !== null && !$this->eiState->isDetailDisabled()) {
			$this->rocketState->addBreadcrumb($this->eiState->createDetailBreadcrumb($this->getHttpContext(), 
					$eiSelection));
		}
	} 
}
