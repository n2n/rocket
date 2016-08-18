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
namespace rocket\spec\ei\component\command\impl\common\controller;

use rocket\spec\ei\manage\ManageState;
use n2n\http\controller\ControllerAdapter;
use n2n\l10n\DynamicTextCollection;
use n2n\http\ParamGet;
use n2n\http\PageNotFoundException;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\http\controller\ParamQuery;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use n2n\l10n\MessageContainer;
use n2n\http\StatusException;

class DeleteController extends ControllerAdapter {
	private $dtc;
	private $utils;
	private $eiCtrlUtils;
	
	public function prepare(ManageState $manageState, DynamicTextCollection $dtc, EiCtrlUtils $eiCtrlUtils) {
		$this->dtc = $dtc;
		$this->utils = new EiStateUtils($manageState->peakEiState());
		$this->eiCtrlUtils = $eiCtrlUtils;
	}
	
	public function doLive($idRep, ParamQuery $refPath = null, MessageContainer $mc) {
		$redirectUrl = $this->eiCtrlUtils->buildRefRedirectUrl($this->eiCtrlUtils->parseRefUrl($refPath));
		
		$eiSelection = null;
		try {
			$eiSelection = $this->eiCtrlUtils->lookupEiSelection($this->utils->idRepToId($idRep));
		} catch (StatusException $e) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$vetoableAction = $this->eiCtrlUtils->getEiStateUtils()->remove($eiSelection);
		if ($vetoableAction->hasVetos()) {
			$mc->addAll($vetoableAction->getReasonMessages());
		}
		
		$this->redirect($redirectUrl);
	}
	
	public function doDraft($id, $draftId, ParamGet $previewtype = null) {
		$eiSelection = null;
		try {
			$eiSelection = $this->utils->createEiSelectionFromDraftId($id, $draftId);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException();
		}
		
		$this->utils->removeEiSelection($eiSelection);
		
		$eiState = $this->utils->getEiState();
		$this->redirect($this->utils->getEiState()->getDetailUrl(
				$eiSelection->toEntryNavPoint($previewtype)->copy(true)));
	}
}
