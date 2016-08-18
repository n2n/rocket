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
use n2n\http\controller\ControllerAdapter;
use n2n\l10n\DateTimeFormat;
use n2n\http\PageNotFoundException;
use rocket\spec\ei\component\command\impl\common\model\DetailModel;
use rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel;
use rocket\spec\ei\manage\EiSelection;

class DetailController extends ControllerAdapter {
	private $dtc;
	private $eiCtrlUtils;
	
	public function prepare(DynamicTextCollection $dtc) {
		$this->dtc = $dtc;
		$this->eiCtrlUtils = EiCtrlUtils::from($this->getHttpContext());
	}
		
	public function doLive($idRep) {
		$eiMapping = $this->eiCtrlUtils->lookupEiMapping($idRep);

		$entryGuiModel = $this->eiCtrlUtils->getEiStateUtils()->createBulkyEntryGuiModel($eiMapping, false);

		$this->applyBreadcrumbs($eiMapping->getEiSelection());
			
		$this->forward('..\view\detail.html', array('entryCommandViewModel' 
				=> new EntryCommandViewModel($this->eiCtrlUtils->getEiStateUtils(), $entryGuiModel)));
	}
	
	public function doDraft($draftId = null) { 
		$eiMapping = $this->eiCtrlUtils->lookupEiMappingByDraftId($draftId);

		$entryGuiModel = $this->eiCtrlUtils->getEiStateUtils()->createBulkyEntryGuiModel($eiMapping, false);
		
		$this->applyBreadcrumbs($eiMapping->getEiSelection());

		$this->forward('..\view\detail.html', array('entryCommandViewModel' 
				=> new EntryCommandViewModel($this->eiCtrlUtils->getEiStateUtils(), $entryGuiModel)));
	}
	
	public function doLivePreview($idRep, $previewType = null) {
		$eiSelection = $this->eiCtrlUtils->lookupEiSelection($idRep);
		
		$eiEntryUtils = $this->eiCtrlUtils->toEiEntryUtils($eiSelection);
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
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiCtrlUtils->getEiStateUtils(), 
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
		$draftModel = null;
		if (isset($draftId)) {
			$detailModel = $this->eiCtrlUtils->createDraftDetailModel($id, $draftId, $httpN2nLocaleId);
		} else {
			$detailModel = $this->eiCtrlUtils->createDetailModel($id, $httpN2nLocaleId, false);
		}
		$previewController = $this->eiCtrlUtils->createPreviewController($detailModel->getEntryInfo(), 
				$this->getRequest(), $this->getResponse(), $previewType);
		$currentPreviewType = $previewController->getPreviewType();
		
// 		if (false != ($redirectUrl = $this->dispatchDetailModel($detailModel))) {
// 			$this->redirect($redirectUrl);
// 			return;
// 		}
		
		$this->applyBreadcrumbs($draftModel->getEiSelection(), $previewType);
		
		$this->forward('spec\ei\component\command\impl\common\view\detailPreview.html', array(
				'commandEntryModel' => $detailModel, 
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext(), 
						array('draftpreviewsrc', $currentPreviewType, $id, $draftId, $httpN2nLocaleId)),
				'entryViewInfo' => new EntryViewInfo($detailModel, $detailModel->getEntryInfo(), $previewController)));
	}
	
	public function doDraftPreviewSrc(array $contextCmds, $previewType, $id, $draftId = null, $httpN2nLocaleId = null) {
		$draftModel = null;
		if (isset($draftId)) {
			$detailModel = $this->eiCtrlUtils->createDraftDetailModel($id, $draftId, $httpN2nLocaleId);
		} else {
			$detailModel = $this->eiCtrlUtils->createDetailModel($id, $httpN2nLocaleId, false);
		}
		$previewController = $this->eiCtrlUtils->createPreviewController($detailModel->getEntryInfo(), $this->getRequest(), 
				$this->getResponse(), $previewType);
	
		$previewController->execute(array(), $contextCmds, $this->getN2nContext());
	}
	
	private function applyBreadcrumbs(EiSelection $eiSelection, string $previewType = null) {
		$this->eiCtrlUtils->applyCommonBreadcrumbs();
		
		$eiState = $this->eiCtrlUtils->getEiState();
		$httpContext = $this->getHttpContext();

		if ($eiState->isDetailDisabled()) return;
		
		$pathParts = null;
		if ($previewType === null || $eiSelection->isDraft()) {
			$pathParts = array('live', $eiSelection->getLiveEntry()->getId());
		} else {
			$pathParts = array('livepreview', $eiSelection->getLiveEntry()->getId(), $previewType);
		}
		
		$this->eiCtrlUtils->applyBreandcrumbs(new Breadcrumb($this->getUrlToController($pathParts), 
				$eiState->getDetailBreadcrumbLabel($eiSelection)));
		
		if ($eiSelection->isDraft()) {
			$pathParts = null;
			if ($previewType === null) {
				$pathParts = array('draft', $eiSelection->getDraft()->getId());
			} else {
				$pathParts = array('draftpreview', $eiSelection->getDraft()->getId(), $previewType);
			}
				
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getN2nLocale());
			$this->eiCtrlUtils->applyBreandcrumbs(new Breadcrumb($this->getUrlToController($pathParts),
					$this->dtc->translate('ei_impl_detail_draft_breadcrumb',
							array('last_mod' => $dtf->format($eiSelection->getDraft()->getLastMod())))));
		}
	}
}