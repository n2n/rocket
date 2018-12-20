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

use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\DateTimeFormat;
use n2n\web\http\PageNotFoundException;
use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
use rocket\ei\manage\EiObject;
use rocket\ei\util\EiuCtrl;

class DetailController extends ControllerAdapter {
	private $dtc;
	private $eiuCtrl;
	
	public function prepare(DynamicTextCollection $dtc, EiuCtrl $eiCtrl) {
		$this->dtc = $dtc;
		$this->eiuCtrl = $eiCtrl;
	}
		
	public function doLive($pid) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		$eiuEntryGui = $eiuEntry->newEntryGui();
		
		$viewModel = new EntryCommandViewModel($this->eiuCtrl->frame(), null, $eiuEntry);
		$viewModel->initializeDrafts();
		
		$this->applyBreadcrumbs($eiuEntryGui->entry()->object()->getEiObject());
			
		$view = $this->createView('..\view\detail.html', array('entryCommandViewModel' => $viewModel,
				'eiuEntryGui' => $eiuEntryGui));
		
		$this->eiuCtrl->forwardView($view);
	}
	
	public function doDraft($draftId) { 
		$eiEntry = $this->eiuCtrl->lookupEiEntryByDraftId($draftId);

		$entryGuiModel = $this->eiuCtrl->frame()->createBulkyEntryGuiModel($eiEntry, false);
		
		$this->applyBreadcrumbs($eiEntry->getEiObject());

		$this->forward('..\view\detail.html', array('entryCommandViewModel' 
				=> new EntryCommandViewModel($this->eiuCtrl->frame(), $entryGuiModel)));
	}
	
	public function doLivePreview($pid, $previewType = null) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		$previewTypeOptions = $eiuEntry->getPreviewTypeOptions();
		if (empty($previewTypeOptions)) {
			throw new PageNotFoundException();
		}
		
		if ($previewType === null) {
			$this->redirectToController(array('preview', $pid, key($previewTypeOptions)));
			return;
		}
		
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiuEntry);
		
		$this->applyBreadcrumbs($eiuEntry->object()->getEiObject(), $previewType);
		
		$this->forward('..\view\detailPreview.html', array( 
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext())
						->ext('livepreviewsrc', $pid, $previewType),
				'currentPreviewType' => $previewType,
				'previewTypeOptions' => $previewTypeOptions, 
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiuCtrl->frame(), null, $eiuEntry)));
	}
	
	public function doLivePreviewSrc($pid, $previewType, array $delegateCmds = array()) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiuEntry);
		
		$this->delegate($previewController);
	}
	
// 	public function doHistoryPublish($id, $draftId, $httpN2nLocaleId = null, ParamGet $previewtype = null) {
// 		$detailModel = $this->utils->createHistoryDetailModel($id, $draftId, $httpN2nLocaleId);
// 		$detailModel->publish();
		
// 		$this->redirectToController(PathUtils::createDetailPathExtFromEntryNavPoint(null, 
// 				$detailModel->getEiObject()->toEntryNavPoint($previewtype)->copy(true)));
// 	}
	
	public function doDraftPreview($draftId, $previewType = null) {
		$eiObject = $this->eiuCtrl->lookupEiObjectByDraftId($draftId);
		
		$eiObjectUtils = $this->eiuCtrl->toEiuEntry($eiObject);
		$previewTypeOptions = $eiObjectUtils->getPreviewTypeOptions();
		if (empty($previewTypeOptions)) {
			throw new PageNotFoundException();
		}
		
		if ($previewType === null) {
			$this->redirectToController(array('draftpreview', $draftId, key($previewTypeOptions)));
			return;
		}
		
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiObject);
		
		$this->applyBreadcrumbs($eiObject, $previewType);
		
		$this->forward('..\view\detailPreview.html', array( 
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext())
						->ext('draftpreviewsrc', $draftId, $previewType),
				'currentPreviewType' => $previewType,
				'previewTypeOptions' => $previewTypeOptions, 
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiuCtrl->frame(), 
						$eiObject)));
	}
	
	
	
	public function doDraftPreviewSrc($draftId, $previewType, array $delegateCmds = array()) {
		$eiObject = $this->eiuCtrl->lookupEiObjectByDraftId($draftId);
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiObject);
		
		$this->delegate($previewController);
	}
	
	private function applyBreadcrumbs(EiObject $eiObject, string $previewType = null) {
		$this->eiuCtrl->applyCommonBreadcrumbs();
		
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$httpContext = $this->getHttpContext();

		if ($eiFrame->isDetailDisabled()) return;
		
		if ($eiObject->getEiEntityObj()->isPersistent()) {
			$pathParts = null;
			if ($previewType === null || $eiObject->isDraft()) {
				$pathParts = array('live', $eiObject->getEiEntityObj()->getId());
			} else {
				$pathParts = array('livepreview', $eiObject->getEiEntityObj()->getId(), $previewType);
			}
			
			$this->eiuCtrl->applyBreadcrumbs(new Breadcrumb($this->getUrlToController($pathParts), 
					$eiFrame->getDetailBreadcrumbLabel($eiObject)));
		}
		
		if ($eiObject->isDraft()) {
			$pathParts = null;
			if ($previewType === null) {
				$pathParts = array('draft', $eiObject->getDraft()->getId());
			} else {
				$pathParts = array('draftpreview', $eiObject->getDraft()->getId(), $previewType);
			}
				
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getN2nLocale());
			
			$breadcrumb = null;
			if ($eiObject->getEiEntityObj()->isPersistent()) {
				$breadcrumb = new Breadcrumb($this->getUrlToController($pathParts),
						$this->dtc->translate('ei_impl_detail_draft_breadcrumb',
								array('last_mod' => $dtf->format($eiObject->getDraft()->getLastMod()))));
			} else {
				$breadcrumb = new Breadcrumb($this->getUrlToController($pathParts),
						$this->dtc->translate('ei_impl_detail_unbound_draft_breadcrumb',
								array('entry' => $this->eiuCtrl->frame()
										->createIdentityString($eiObject))));
			}
			
			$this->eiuCtrl->applyBreadcrumbs($breadcrumb);
		}
	}
}
