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
use rocket\impl\ei\component\command\common\model\EditModel;
use n2n\web\http\controller\ControllerAdapter;
use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
use n2n\util\uri\Url;
use rocket\spec\ei\manage\EiObject;
use rocket\core\model\Breadcrumb;
use n2n\web\http\controller\ParamQuery;
use n2n\l10n\DateTimeFormat;
use rocket\spec\ei\manage\draft\Draft;
use n2n\util\col\ArrayUtils;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\ajah\JhtmlEvent;

class EditController extends ControllerAdapter {
	private $dtc;
	private $eiuCtrl;
	
	public function prepare(DynamicTextCollection $dtc, EiuCtrl $eiuCtrl, Eiu $eiu) {
		$this->dtc = $dtc;
		$this->eiuCtrl = $eiuCtrl;
	}
	
	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @param Url $cancelUrl
	 * @return \rocket\impl\ei\component\command\common\model\EntryCommandViewModel
	 */
	private function createEntryCommandViewModel(EiuEntryGui $eiuEntryGui, Url $cancelUrl = null) {
		$viewModel = new EntryCommandViewModel($eiuEntryGui->getEiuEntry()->getEiuFrame(), $cancelUrl, 
				$eiuEntryGui->getEiuEntry());
		$viewModel->initializeDrafts();
		return $viewModel;
	}
	
	public function doLive($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$eiEntry = $this->eiuCtrl->lookupEiEntry($idRep);
		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
		$editModel->initialize($eiEntry);

		if ($this->dispatch($editModel, 'save')) {
			$this->eiuCtrl->redirectBack($redirectUrl, JhtmlEvent::ei()->eiObjectChanged($eiEntry));
			return;
		}
		
		$jhtmlEvent = null;
		if ($this->dispatch($editModel, 'quicksave')) {
			$jhtmlEvent = JhtmlEvent::ei()->eiObjectChanged($eiEntry);
			$this->refresh();
			return;
		} else if ($this->dispatch($editModel, 'saveAndPreview')) {
			$jhtmlEvent = JhtmlEvent::ei()->eiObjectChanged($eiEntry);
			$defaultPreviewType = key($this->eiuCtrl->frame()->getPreviewTypeOptions($editModel->getEntryModel()->getEiuEntryGui()->getEiuEntry()->getEiObject()));
			$this->eiuCtrl->redirect($this->getUrlToController(['livepreview', $idRep, $defaultPreviewType],
					array('refPath' => (string) $redirectUrl)), $jhtmlEvent);
			return;
		}
		
		$this->eiuCtrl->applyCommonBreadcrumbs($eiEntry->getEiObject(), 
				$this->dtc->translate('ei_impl_edit_entry_breadcrumb'));
		
		$view = $this->createView('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel(
						$editModel->getEntryModel()->getEiuEntryGui(), $redirectUrl)));
		$this->eiuCtrl->forwardView($view, $jhtmlEvent);
	}
	
	public function doLivePreview($idRep, $previewType, ParamQuery $refPath) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($idRep);
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiuEntry);
		
		$previewTypeOptions = $eiuEntry->getPreviewTypeOptions();
		
		$this->eiuCtrl->applyCommonBreadcrumbs($eiuEntry);
		
		$view = $this->createView('..\view\editPreview.html', array(
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext())
						->ext('livepreviewsrc', $idRep, $previewType),
				'currentPreviewType' => $previewType,
				'previewTypeOptions' => $previewTypeOptions,
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiuCtrl->frame(), $redirectUrl, $eiuEntry)));
		$this->eiuCtrl->forwardView($view);
	}
	
	public function doLivePreviewSrc($idRep, $previewType, array $delegateCmds = array()) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($idRep);
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiuEntry);
		
		$this->delegate($previewController);
	}
	
	public function doLatestDraft($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$eiObject = $this->eiuCtrl->lookupEiObject($idRep);
		$drafts = $this->eiuCtrl->frame()->toEiuEntry($eiObject)->lookupDrafts(0, 1);
		$draft = ArrayUtils::first($drafts);
		if ($draft === null || $draft->isPublished()) {
			$this->redirectToController(array('newdraft', $idRep), array('refPath' => $refPath));
			return;
		}
		
		$this->redirectToController(array('newdraft', $idRep), array('refPath' => $refPath));
	}
		
	public function doNewDraft($idRep, ParamQuery $refPath) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$eiEntry = $this->eiuCtrl->lookupEiEntry($idRep);
		$entryEiUtils = $this->eiuCtrl->toEiuEntry($eiEntry);
		
		$eiUtils = $this->eiuCtrl->frame();
		$draftEiObject = $eiUtils->createEiObjectFromDraft(
				$eiUtils->createNewDraftFromEiEntityObj($eiEntry->getEiObject()->getEiEntityObj()));
		$draftEiEntry = $this->eiuCtrl->frame()->createEiEntryCopy($eiEntry, $draftEiObject);
		
		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
		$editModel->initialize($draftEiEntry);
		
		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->eiuCtrl->applyCommonBreadcrumbs($eiEntry->getEiObject(),
				$this->dtc->translate('ei_impl_edit_new_draft_breadcrumb'));
		
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
						->getEntryGuiModel(), $redirectUrl)));
	}
	
	public function doDraft($draftId, ParamQuery $refPath) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$eiEntry = $this->eiuCtrl->lookupEiEntryByDraftId($draftId);
		$entryEiUtils = $this->eiuCtrl->toEiuEntry($eiEntry);
		if ($entryEiUtils->getDraft()->isPublished()) {
			$eiObject = $entryEiUtils->getEiUtils()->createNewEiObject(true, $entryEiUtils->getEiType());
			$eiEntry = $this->eiuCtrl->frame()->createEiEntryCopy($eiEntry, $eiObject);
		}
		
		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
		$editModel->initialize($eiEntry);
	
		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}

		$this->eiuCtrl->applyCommonBreadcrumbs($eiEntry->getEiObject(), 
				$this->dtc->translate('ei_impl_edit_draft_breadcrumb'));
	
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
						->getEntryGuiModel(), $redirectUrl)));
	}
	
	public function doPublish($draftId, ParamQuery $refPath) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$draftEiEntry = $this->eiuCtrl->lookupEiEntryByDraftId($draftId);
		
		$eiUtils = $this->eiuCtrl->frame();
		$eiObject = $eiUtils->createEiObjectFromEiEntityObj($draftEiEntry->getEiObject()->getEiEntityObj());
		$eiEntry = $eiUtils->createEiEntryCopy($draftEiEntry, $eiObject);
		
		if ($eiEntry->save()) {
			$eiUtils->persist($eiObject);
			$draft = $draftEiEntry->getEiObject()->getDraft();
			$draft->setType(Draft::TYPE_PUBLISHED);
			$eiUtils->persist($draft);
			
			$this->redirect($this->eiuCtrl->buildRedirectUrl($eiEntry->getEiObject()));
			return;
		}
		
		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
		$editModel->initialize($eiEntry);
		
		if ($this->dispatch($editModel, 'save')) {
			$this->redirect($redirectUrl);
			return;
		}
		
		$this->eiuCtrl->applyCommonBreadcrumbs($draftEiEntry->getEiObject(),
				$this->dtc->translate('ei_impl_publish_entry_breadcrumb'));
		
		$this->forward('..\view\edit.html', array('editModel' => $editModel,
				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
						->getEntryGuiModel(), $redirectUrl)));
	}
	
// 	public function doPreview($idRep, $previewType = null, ParamGet $refPath = null) {
// 		$redirectUrl = $this->buildRedirectUrl($refPath);
		
// 		$eiEntry = $this->controllingUtils->lookupEiEntry($idRep, true, $draftId);
// 		$entryManager = $this->utils->createEntryManager($eiEntry);
// 		$entryForm = $this->utils->createEntryForm($eiEntry);
		
// 		$this->utils->lookupEditablePreivewEi		
// 		$previewController = $this->utils->createPreviewController($editModel->getEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType, $editModel);
// 		$currentPreviewType = $previewController->getPreviewType();
				
// 		$this->applyBreadcrumbs($eiFrame);
		
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
// 		$eiFrame = $this->utils->getEiFrame();
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
	
	private function applyBreadcrumbs(EiObject $eiObject) {
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$httpContext = $this->getHttpContext();
				
		if (!$eiFrame->isOverviewDisabled()) {
			$this->rocketState->addBreadcrumb(
					$eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
		}
		
		$this->rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($httpContext, $eiObject));
		
		if ($eiObject->isDraft()) {	
			$breadcrumbPath = $eiFrame->getDetailUrl($httpContext, $eiObject->toEntryNavPoint($eiFrame->getContextEiMask()->getEiEngine()->getEiType())
							->copy(false, true));
			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getN2nLocale(),
					DateTimeFormat::STYLE_MEDIUM, DateTimeFormat::STYLE_SHORT);
			$breadcrumbLabel = $this->dtc->translate('ei_impl_detail_draft_breadcrumb', 
					array('last_mod' => $dtf->format($eiObject->getDraft()->getLastMod())));
			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
		}
		
	}	
	
// 	private function dispatchEditModel(EditModel $editModel) {
// 		$eiFrame = $this->utils->getEiFrame();
// 		$entryNavPoint = null;
		
// 		$dispReturn = $this->dispatch($editModel, 'save');
// 		$eiObject = $editModel->getEntryModel()->getEiEntry()->getEiObject();
// 		if ($dispReturn instanceof Draft) {
// 			$entryNavPoint = $eiObject->toEntryNavPoint($eiFrame->getContextEiMask()->getEiEngine()->getEiType());
// 		} else if ($dispReturn) {
// 			$entryNavPoint = $eiObject->toEntryNavPoint($eiFrame->getContextEiMask()->getEiEngine()->getEiType())->copy(true);
// 		} else {
// 			return null;
// 		}
		
// 		return $eiFrame->getDetailUrl($this->getRequest(), $entryNavPoint);
// 	}
}
