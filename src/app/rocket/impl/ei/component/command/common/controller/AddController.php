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

use rocket\core\model\RocketState;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\controller\ControllerAdapter;
use rocket\impl\ei\component\command\common\model\AddModel;
use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
use n2n\web\http\controller\ParamGet;
use rocket\ei\util\EiuCtrl;
use n2n\web\dispatch\map\PropertyPath;

class AddController extends ControllerAdapter {
	private $dtc;
	private $rocketState;
	private $eiuCtrl;
	
	private $parentEiObject;
	private $beforeEiObject;
	private $afterEiObject;
	
	public function prepare(DynamicTextCollection $dtc, EiuCtrl $eiCtrl, RocketState $rocketState) {
		$this->dtc = $dtc;
		$this->eiuCtrl = $eiCtrl;
	}
		
	public function index($copyPid = null, ParamGet $refPath = null) {	
		$this->live($refPath, $copyPid);
	}
	
	public function doChild($parentPid, $copyPid = null, ParamGet $refPath = null) {
		$this->parentEiObject = $this->eiuCtrl->lookupEiObject($parentPid);	
		$this->live($refPath, $copyPid);	
	}
	
	public function doBefore($beforePid, $copyPid = null, ParamGet $refPath = null) {
		$this->beforeEiObject = $this->eiuCtrl->lookupEiObject($beforePid);	
		$this->live($refPath, $copyPid);
	}
	
	public function doAfter($afterPid, $copyPid = null, ParamGet $refPath = null) {
		$this->afterEiObject = $this->eiuCtrl->lookupEiObject($afterPid);	
		$this->live($refPath, $copyPid);
	}
	
	private function live(ParamGet $refPath = null, $copyPid = null) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		$eiuFrame = $this->eiuCtrl->frame();
		
		$copyFrom = null;
		if ($copyPid !== null) {
			$copyFrom = $this->eiuCtrl->lookupEntry($copyPid);
		}
		
		$eiuEntryForm = $eiuFrame->newEntryForm(false, $copyFrom, new PropertyPath(array('eiuEntryForm')));
		
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$addModel = new AddModel($eiFrame, $eiuEntryForm, $eiuFrame->getNestedSetStrategy());
		if ($this->parentEiObject !== null) {
			$addModel->setParentEntityObj($this->parentEiObject->getLiveObject());
		} else if ($this->beforeEiObject !== null) {
			$addModel->setBeforeEntityObj($this->beforeEiObject->getLiveObject());
		} else if ($this->afterEiObject !== null) {
			$addModel->setAfterEntityObj($this->afterEiObject->getLiveObject());
		}
		
		if (is_object($eiObject = $this->dispatch($addModel, 'create'))) {
			$this->eiuCtrl->redirectBack($this->eiuCtrl->buildRefRedirectUrl($redirectUrl, $eiObject));
			return;
		} else if ($this->dispatch($addModel, 'createAndRepeate')) {
			$this->refresh();
			return;
		}
		
		$this->eiuCtrl->applyCommonBreadcrumbs(null, $this->getBreadcrumbLabel());
		
		$viewModel = new EntryCommandViewModel($this->eiuCtrl->frame(), $redirectUrl);
		$viewModel->setTitle($this->dtc->translate('ei_impl_add_title', array(
				'type' => $this->eiuCtrl->frame()->getEiFrame()->getContextEiEngine()->getEiMask()->getLabelLstr()
						->t($this->getN2nContext()->getN2nLocale()))));
		
		$view = $this->createView('..\view\add.html',
				array('addModel' => $addModel, 'entryViewInfo' => $viewModel));
		$this->eiuCtrl->forwardView($view);
	}
	
	public function doDraft(ParamGet $refPath = null) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
			
		$eiuEntryForm = $this->eiuCtrl->frame()->newEntryForm(true);
		
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$addModel = new AddModel($eiFrame, $eiuEntryForm);
		
		if (is_object($eiObject = $this->dispatch($addModel, 'create'))) {
			$this->redirect($this->eiuCtrl->buildRefRedirectUrl($redirectUrl, $eiObject));
			return;
		}
		
		$viewModel = new EntryCommandViewModel($this->eiuCtrl->frame(), null, $redirectUrl);
		$viewModel->setTitle($this->dtc->translate('ei_impl_add_draft_title', 
				array('type' => $this->eiuCtrl->frame()->getGenericLabel())));
		$this->forward('..\view\add.html', array('addModel' => $addModel, 'entryViewInfo' => $viewModel));
	}
	
	private function getBreadcrumbLabel() {
		$eiFrameUtils = $this->eiuCtrl->frame();
		
		if (null === $eiFrameUtils->getNestedSetStrategy()) {
			return $this->dtc->translate('ei_impl_add_breadcrumb');
		} else if ($this->parentEiObject !== null) {
			return $this->dtc->translate('ei_impl_add_child_branch_breadcrumb',
					array('parent_branch' => $eiFrameUtils->createIdentityString($this->parentEiObject)));
		} else if ($this->beforeEiObject !== null) {
			return$this->dtc->translate('ei_impl_add_before_branch_breadcrumb',
					array('branch' => $eiFrameUtils->createIdentityString($this->beforeEiObject)));
		} else if ($this->afterEiObject !== null) {
			return $this->dtc->translate('ei_impl_add_after_branch_breadcrumb',
					array('branch' => $eiFrameUtils->createIdentityString($this->afterEiObject)));
		} else {
			return $this->dtc->translate('ei_impl_add_root_branch_breadcrumb');
		}
	}
}
