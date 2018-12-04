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
namespace rocket\impl\ei\component\command\common\controller;

use rocket\ei\manage\ManageState;
use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\controller\ParamQuery;
use n2n\l10n\MessageContainer;
use n2n\web\http\StatusException;
use rocket\ei\util\EiuCtrl;
use rocket\ajah\JhtmlEvent;
use rocket\ei\util\Eiu;

class DeleteController extends ControllerAdapter {
	private $dtc;
	private $utils;
	private $eiuCtrl;
	
	public function prepare(ManageState $manageState, DynamicTextCollection $dtc, EiuCtrl $eiuCtrl) {
		$this->dtc = $dtc;
		$this->utils = (new Eiu($manageState->peakEiFrame()))->frame();
		$this->eiuCtrl = $eiuCtrl;
	}
	
	public function doLive($pid, ParamQuery $refPath = null, MessageContainer $mc) {
		$redirectUrl = $this->eiuCtrl->buildRefRedirectUrl($this->eiuCtrl->parseRefUrl($refPath));
		
		$eiObject = null;
		try {
			$eiObject = $this->eiuCtrl->lookupEiObject($this->utils->pidToId($pid));
		} catch (StatusException $e) {
			$this->eiuCtrl->redirectToReferer($redirectUrl);
			return;
		}
		
		$eiuFrame = $this->eiuCtrl->frame();
		$eiuFrame->remove($eiObject);
		$taa = $eiuFrame->flush();
		
		$evt = null;
		if ($taa->isSuccessful()) {
			$evt = JhtmlEvent::ei()->eiObjectRemoved($eiObject)->noAutoEvents();
		} else {
			$evt = JhtmlEvent::ei()->message(...$taa->getReasonMessages())->noAutoEvents();
		}
		
		$this->eiuCtrl->redirectToReferer($redirectUrl, $evt);
	}
	
// 	public function doDraft($id, $draftId, ParamGet $previewtype = null) {
// 		$eiObject = null;
// 		try {
// 			$eiObject = $this->utils->createEiObjectFromDraftId($id, $draftId);
// 		} catch (\InvalidArgumentException $e) {
// 			throw new PageNotFoundException();
// 		}
		
// 		$this->utils->removeEiObject($eiObject);
		
// 		$eiFrame = $this->utils->getEiFrame();
// 		$this->redirect($this->utils->getEiFrame()->getDetailUrl(
// 				$eiObject->toEntryNavPoint($previewtype)->copy(true)));
// 	}
}
