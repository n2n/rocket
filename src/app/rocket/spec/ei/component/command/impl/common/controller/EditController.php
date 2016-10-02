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

use rocket\core\model\RocketState;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\component\command\impl\common\model\EditModel;
use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel;
use n2n\util\uri\Url;
use rocket\spec\ei\manage\EiSelection;
use rocket\core\model\Breadcrumb;
use n2n\web\http\controller\ParamQuery;
use n2n\l10n\DateTimeFormat;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\draft\Draft;
use n2n\util\col\ArrayUtils;

class EditController extends ControllerAdapter {
	private $dtc;
	private $eiCtrlUtils;
	
	public function prepare(DynamicTextCollection $dtc) {
		$this->dtc = $dtc;
		$this->eiCtrlUtils = EiCtrlUtils::from($this->getHttpContext());
	}
	
	/**
	 * @param EntryModel $entryGuiModel
	 * @param Url $cancelUrl
	 * @return \rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel
	 */
	private function createEntryCommandViewModel(EntryGuiModel $entryGuiModel, Url $cancelUrl = null) {
		$viewModel = new EntryCommandViewModel($this->eiCtrlUtils->getEiStateUtils(), $entryGuiModel, $cancelUrl);
		$viewModel->initializeDrafts();
		return $viewModel;
	}

	
	public function doLive($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		$eiMapping = $this->eiCtrlUtils->lookupEiMapping($idRep);
		$editModel = new EditModel($this->eiCtrlUtils->getEiStateUtils(), true, true);
		$editModel->initialize($eiMapping);

		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->eiCtrlUtils->applyCommonBreadcrumbs($eiMapping->getEiSelection(), 
				$this->dtc->translate('ei_impl_edit_entry_breadcrumb'));
		
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel(
						$editModel->getEntryModel()->getEntryGuiModel(), $redirectUrl)));
	}
	
	public function doLatestDraft($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		$eiSelection = $this->eiCtrlUtils->lookupEiSelection($idRep);
		$drafts = $this->eiCtrlUtils->getEiStateUtils()->toEiEntryUtils($eiSelection)->lookupDrafts(0, 1);
		$draft = ArrayUtils::first($drafts);
		if ($draft === null || $draft->isPublished()) {
			$this->redirectToController(array('newdraft', $idRep), array('refPath' => $refPath));
			return;
		}
		
		$this->redirectToController(array('newdraft', $idRep), array('refPath' => $refPath));
	}
		
	public function doNewDraft($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		$eiMapping = $this->eiCtrlUtils->lookupEiMapping($idRep);
		$entryEiUtils = $this->eiCtrlUtils->toEiEntryUtils($eiMapping);
		
		$eiUtils = $this->eiCtrlUtils->getEiStateUtils();
		$draftEiSelection = $eiUtils->createEiSelectionFromDraft(
				$eiUtils->createNewDraftFromLiveEntry($eiMapping->getEiSelection()->getLiveEntry()));
		$draftEiMapping = $this->eiCtrlUtils->getEiStateUtils()->createEiMappingCopy($draftEiSelection, $eiMapping);
		
		$editModel = new EditModel($this->eiCtrlUtils->getEiStateUtils(), true, true);
		$editModel->initialize($draftEiMapping);
		
		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->eiCtrlUtils->applyCommonBreadcrumbs($eiMapping->getEiSelection(),
				$this->dtc->translate('ei_impl_edit_new_draft_breadcrumb'));
		
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
						->getEntryGuiModel(), $redirectUrl)));
	}
	
	public function doDraft($draftId, ParamQuery $refPath) {
		$redirectUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		$eiMapping = $this->eiCtrlUtils->lookupEiMappingByDraftId($draftId);
		$entryEiUtils = $this->eiCtrlUtils->toEiEntryUtils($eiMapping);
		if ($entryEiUtils->getDraft()->isPublished()) {
			$eiSelection = $entryEiUtils->getEiUtils()->createNewEiSelection(true, $entryEiUtils->getEiSpec());
			$eiMapping = $this->eiCtrlUtils->getEiStateUtils()->createEiMappingCopy($eiSelection, $eiMapping);
		}
		
		$editModel = new EditModel($this->eiCtrlUtils->getEiStateUtils(), true, true);
		$editModel->initialize($eiMapping);
	
		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}

		$this->eiCtrlUtils->applyCommonBreadcrumbs($eiMapping->getEiSelection(), 
				$this->dtc->translate('ei_impl_edit_draft_breadcrumb'));
	
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
						->getEntryGuiModel(), $redirectUrl)));
	}
	
	public function doPublish($draftId, ParamQuery $refPath) {
		$redirectUrl = $this->eiCtrlUtils->parseRefUrl($refPath);
		
		$draftEiMapping = $this->eiCtrlUtils->lookupEiMappingByDraftId($draftId);
		
		$eiUtils = $this->eiCtrlUtils->getEiStateUtils();
		$eiSelection = $eiUtils->createEiSelectionFromLiveEntry($draftEiMapping->getEiSelection()->getLiveEntry());
		$eiMapping = $eiUtils->createEiMappingCopy($eiSelection, $draftEiMapping);
		
		if ($eiMapping->save()) {
			$eiUtils->persist($eiSelection);
			$draft = $draftEiMapping->getEiSelection()->getDraft();
			$draft->setType(Draft::TYPE_PUBLISHED);
			$eiUtils->persist($draft);
			
			$this->redirect($this->eiCtrlUtils->buildRedirectUrl($eiMapping->getEiSelection()));
			return;
		}
		
		$editModel = new EditModel($this->eiCtrlUtils->getEiStateUtils(), true, true);
		$editModel->initialize($eiMapping);
		
		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->eiCtrlUtils->applyCommonBreadcrumbs($draftEiMapping->getEiSelection(),
				$this->dtc->translate('ei_impl_publish_entry_breadcrumb'));
		
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
						->getEntryGuiModel(), $redirectUrl)));
	}
	
// 	public function doPreview($idRep, $previewType = null, ParamGet $refPath = null) {
// 		$redirectUrl = $this->buildRedirectUrl($refPath);
		
// 		$eiMapping = $this->controllingUtils->lookupEiMapping($idRep, true, $draftId);
// 		$entryManager = $this->utils->createEntryManager($eiMapping);
// 		$entryForm = $this->utils->createEntryForm($eiMapping);
		
// 		$this->utils->lookupEditablePreivewEi		
// 		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType, $editModel);
// 		$currentPreviewType = $previewController->getPreviewType();
				
// 		$this->applyBreadcrumbs($eiState);
		
// 		$this->forward('spec\ei\component\command\impl\common\view\editPreview.html', array('commandEditEntryModel' => $editModel,
// 				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext(),
// 						array('previewsrc', $currentPreviewType, $id, $httpN2nLocaleId)),
// 				'entryViewInfo' => new EntryViewInfo($editModel, $editModel->getEntryForm(), $previewController)));
// 	}
	
// 	public function doPreviewSrc(array $contextCmds, 
// 			array $cmds, $previewType, $id, $httpN2nLocaleId = null) {
// 		$editModel = $this->utils->createEditModel($id, $httpN2nLocaleId, true, $this->editCommand);

// 		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType, $editModel);

// 		if (null != ($redirectUrl = $this->dispatchEditModel($editModel, false, true))) {
// 			$previewController->getPreviewModel()->setRedirectUrl($redirectUrl);
// 		}
		
// 		$previewController->execute(array(), array_merge($contextCmds, $cmds), $this->getN2nContext());
// 	}
	
// 	public function doDraftPreview($previewType, $id, $draftId, $httpN2nLocaleId = null) {
// 		$eiState = $this->utils->getEiState();
// 		$editModel = $this->utils->createDraftEditModel($id, $draftId, $httpN2nLocaleId, $this->editCommand);
// 		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType);
// 		$currentPreviewType = $previewController->getPreviewType();
		
// 		$this->applyBreadcrumbs($editModel);
		
// 		$this->forward('spec\ei\component\command\impl\common\view\editPreview.html', array('commandEditEntryModel' => $editModel, 
// 				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext(), 
// 						array('draftpreviewsrc', $currentPreviewType, $id, $draftId, $httpN2nLocaleId)),
// 				'entryViewInfo' => new EntryViewInfo($editModel, $editModel->getEntryForm(), $previewController)));
// 	}
	
// 	public function doDraftPreviewSrc(array $contextCmds, array $cmds, $previewType, $id, $draftId, $httpN2nLocaleId = null) {
// 		$editModel = $this->utils->createDraftEditModel($id, $draftId, $httpN2nLocaleId, $this->editCommand);
// 		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), 
// 				$this->getRequest(), $this->getResponse(), $previewType, $editModel);
	
// 		if (null != ($redirectUrl = $this->dispatchEditModel($editModel, true, true, $this->editCommand))) {
// 			$previewController->getPreviewModel()->setRedirectUrl($redirectUrl);
// 		}
		
// 		$previewController->execute(array(), array_merge($contextCmds, $cmds), $this->getN2nContext());
// 	}
	
	private function applyBreadcrumbs(EiSelection $eiSelection) {
		$eiState = $this->eiCtrlUtils->getEiStateUtils()->getEiState();
		$httpContext = $this->getHttpContext();
				
		if (!$eiState->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb(
					$eiState->createOverviewBreadcrumb($this->getHttpContext()));
		}
		
		$this->rocketState->addBreadcrumb($eiState->createDetailBreadcrumb($httpContext, $eiSelection));
		
		if ($eiSelection->isDraft()) {	
			$breadcrumbPath = $eiState->getDetailUrl($httpContext, $eiSelection->toEntryNavPoint($eiState->getContextEiMask()->getEiEngine()->getEiSpec())
							->copy(false, true));
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getN2nLocale(),
					DateTimeFormat::STYLE_MEDIUM, DateTimeFormat::STYLE_SHORT);
			$breadcrumbLabel = $this->dtc->translate('ei_impl_detail_draft_breadcrumb', 
					array('last_mod' => $dtf->format($eiSelection->getDraft()->getLastMod())));
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
		}
		
	}	
	
// 	private function dispatchEditModel(EditModel $editModel) {
// 		$eiState = $this->utils->getEiState();
// 		$entryNavPoint = null;
		
// 		$dispReturn = $this->dispatch($editModel, 'save');
// 		$eiSelection = $editModel->getEntryModel()->getEiMapping()->getEiSelection();
// 		if ($dispReturn instanceof Draft) {
// 			$entryNavPoint = $eiSelection->toEntryNavPoint($eiState->getContextEiMask()->getEiEngine()->getEiSpec());
// 		} else if ($dispReturn) {
// 			$entryNavPoint = $eiSelection->toEntryNavPoint($eiState->getContextEiMask()->getEiEngine()->getEiSpec())->copy(true);
// 		} else {
// 			return null;
// 		}
		
// 		return $eiState->getDetailUrl($this->getRequest(), $entryNavPoint);
// 	}
}
