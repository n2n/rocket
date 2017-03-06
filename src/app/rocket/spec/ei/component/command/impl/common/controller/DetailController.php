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

use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\DateTimeFormat;
use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\util\model\EiuGui;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class DetailController extends ControllerAdapter {
	private $dtc;
	private $eiCtrlUtils;
	
	public function prepare(DynamicTextCollection $dtc, EiuCtrl $eiCtrlUtils) {
		$this->dtc = $dtc;
		$this->eiCtrlUtils = $eiCtrlUtils;
	}
		
	public function doLive($idRep) {
		$eiMapping = $this->eiCtrlUtils->lookupEiMapping($idRep);

		$entryGuiModel = $this->eiCtrlUtils->frame()->createBulkyEntryGuiModel($eiMapping, false);
		$entryGuiUtils = new EiuGui($entryGuiModel, $this->eiCtrlUtils);

		$viewModel = new EntryCommandViewModel($this->eiCtrlUtils->frame(), $entryGuiModel);
		$viewModel->initializeDrafts();
		
		$this->applyBreadcrumbs($eiMapping->getEiSelection());
			
		$this->forward('..\view\detail.html', array('entryCommandViewModel' => $viewModel));
	}
	
	public function doDraft($draftId) { 
		$eiMapping = $this->eiCtrlUtils->lookupEiMappingByDraftId($draftId);

		$entryGuiModel = $this->eiCtrlUtils->frame()->createBulkyEntryGuiModel($eiMapping, false);
		
		$this->applyBreadcrumbs($eiMapping->getEiSelection());

		$this->forward('..\view\detail.html', array('entryCommandViewModel' 
				=> new EntryCommandViewModel($this->eiCtrlUtils->frame(), $entryGuiModel)));
	}
	
	public function doLivePreview($idRep, $previewType = null) {
		$eiSelection = $this->eiCtrlUtils->lookupEiSelection($idRep);
		
		$eiEntryUtils = $this->eiCtrlUtils->toEiuEntry($eiSelection);
		$previewTypeOptions = $eiEntryUtils->getPreviewTypeOptions();
		if (empty($previewTypeOptions)) {
			throw new PageNotFoundException();
		}
		
		if ($previewType === null) {
			$this->redirectToController(array('preview', $idRep, key($previewTypeOptions)));
			return;
		}
		
		$previewController = $this->eiCtrlUtils->lookupPreviewController($previewType, $eiSelection);
		
		$this->applyBreadcrumbs($eiSelection, $previewType);
		
		$this->forward('..\view\detailPreview.html', array( 
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext())
						->ext('livepreviewsrc', $idRep, $previewType),
				'currentPreviewType' => $previewType,
				'previewTypeOptions' => $previewTypeOptions, 
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiCtrlUtils->frame(), 
						$eiSelection)));
	}
	
	public function doLivePreviewSrc($idRep, $previewType, array $delegateCmds = array()) {
		$eiSelection = $this->eiCtrlUtils->lookupEiSelection($idRep);
		$previewController = $this->eiCtrlUtils->lookupPreviewController($previewType, $eiSelection);
		
		$this->delegate($previewController);
	}
	
// 	public function doHistoryPublish($id, $draftId, $httpN2nLocaleId = null, ParamGet $previewtype = null) {
// 		$detailModel = $this->utils->createHistoryDetailModel($id, $draftId, $httpN2nLocaleId);
// 		$detailModel->publish();
		
// 		$this->redirectToController(PathUtils::createDetailPathExtFromEntryNavPoint(null, 
// 				$detailModel->getEiSelection()->toEntryNavPoint($previewtype)->copy(true)));
// 	}
	
	public function doDraftPreview($draftId, $previewType = null) {
		$eiSelection = $this->eiCtrlUtils->lookupEiSelectionByDraftId($draftId);
		
		$eiEntryUtils = $this->eiCtrlUtils->toEiuEntry($eiSelection);
		$previewTypeOptions = $eiEntryUtils->getPreviewTypeOptions();
		if (empty($previewTypeOptions)) {
			throw new PageNotFoundException();
		}
		
		if ($previewType === null) {
			$this->redirectToController(array('draftpreview', $draftId, key($previewTypeOptions)));
			return;
		}
		
		$previewController = $this->eiCtrlUtils->lookupPreviewController($previewType, $eiSelection);
		
		$this->applyBreadcrumbs($eiSelection, $previewType);
		
		$this->forward('..\view\detailPreview.html', array( 
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext())
						->ext('draftpreviewsrc', $draftId, $previewType),
				'currentPreviewType' => $previewType,
				'previewTypeOptions' => $previewTypeOptions, 
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiCtrlUtils->frame(), 
						$eiSelection)));
	}
	
	
	
	public function doDraftPreviewSrc($draftId, $previewType, array $delegateCmds = array()) {
		$eiSelection = $this->eiCtrlUtils->lookupEiSelectionByDraftId($draftId);
		$previewController = $this->eiCtrlUtils->lookupPreviewController($previewType, $eiSelection);
		
		$this->delegate($previewController);
	}
	
	private function applyBreadcrumbs(EiSelection $eiSelection, string $previewType = null) {
		$this->eiCtrlUtils->applyCommonBreadcrumbs();
		
		$eiFrame = $this->eiCtrlUtils->getEiFrame();
		$httpContext = $this->getHttpContext();

		if ($eiFrame->isDetailDisabled()) return;
		
		if ($eiSelection->getLiveEntry()->isPersistent()) {
			$pathParts = null;
			if ($previewType === null || $eiSelection->isDraft()) {
				$pathParts = array('live', $eiSelection->getLiveEntry()->getId());
			} else {
				$pathParts = array('livepreview', $eiSelection->getLiveEntry()->getId(), $previewType);
			}
			
			$this->eiCtrlUtils->applyBreandcrumbs(new Breadcrumb($this->getUrlToController($pathParts), 
					$eiFrame->getDetailBreadcrumbLabel($eiSelection)));
		}
		
		if ($eiSelection->isDraft()) {
			$pathParts = null;
			if ($previewType === null) {
				$pathParts = array('draft', $eiSelection->getDraft()->getId());
			} else {
				$pathParts = array('draftpreview', $eiSelection->getDraft()->getId(), $previewType);
			}
				
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getN2nLocale());
			
			$breadcrumb = null;
			if ($eiSelection->getLiveEntry()->isPersistent()) {
				$breadcrumb = new Breadcrumb($this->getUrlToController($pathParts),
						$this->dtc->translate('ei_impl_detail_draft_breadcrumb',
								array('last_mod' => $dtf->format($eiSelection->getDraft()->getLastMod()))));
			} else {
				$breadcrumb = new Breadcrumb($this->getUrlToController($pathParts),
						$this->dtc->translate('ei_impl_detail_unbound_draft_breadcrumb',
								array('entry' => $this->eiCtrlUtils->frame()
										->createIdentityString($eiSelection))));
			}
			
			$this->eiCtrlUtils->applyBreandcrumbs($breadcrumb);
		}
	}
}