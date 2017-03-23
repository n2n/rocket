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
use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\component\command\impl\common\model\AddModel;
use rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel;
use n2n\web\http\controller\ParamGet;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class AddController extends ControllerAdapter {
	private $dtc;
	private $rocketState;
	private $eiuCtrl;
	
	private $parentEiEntry;
	private $beforeEiEntry;
	private $afterEiEntry;
	
	public function prepare(DynamicTextCollection $dtc, EiuCtrl $eiCtrlUtils, RocketState $rocketState) {
		$this->dtc = $dtc;
		$this->eiuCtrl = $eiCtrlUtils;
	}
		
	public function index($copyIdRep = null, ParamGet $refPath = null) {	
		$this->live($refPath, $copyIdRep);
	}
	
	public function doChild($parentIdRep, $copyIdRep = null, ParamGet $refPath = null) {
		$this->parentEiEntry = $this->eiuCtrl->lookupEiEntry($parentIdRep);	
		$this->live($refPath, $copyIdRep);	
	}
	
	public function doBefore($beforeIdRep, $copyIdRep = null, ParamGet $refPath = null) {
		$this->beforeEiEntry = $this->eiuCtrl->lookupEiEntry($beforeIdRep);	
		$this->live($refPath, $copyIdRep);
	}
	
	public function doAfter($afterIdRep, $copyIdRep = null, ParamGet $refPath = null) {
		$this->afterEiEntry = $this->eiuCtrl->lookupEiEntry($afterIdRep);	
		$this->live($refPath, $copyIdRep);
	}
	
	private function live(ParamGet $refPath = null, $copyIdRep = null) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		$eiuFrame = $this->eiuCtrl->frame();
		
		$copyFrom = null;
		if ($copyIdRep !== null) {
			$copyFrom = $this->eiuCtrl->lookupEiMapping($copyIdRep);
		}
		
		$entryForm = $eiuFrame->createNewEntryForm(false, $copyFrom);
		
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$addModel = new AddModel($eiFrame, $entryForm, $eiuFrame->getNestedSetStrategy());
		if ($this->parentEiEntry !== null) {
			$addModel->setParentEntityObj($this->parentEiEntry->getLiveObject());
		} else if ($this->beforeEiEntry !== null) {
			$addModel->setBeforeEntityObj($this->beforeEiEntry->getLiveObject());
		} else if ($this->afterEiEntry !== null) {
			$addModel->setAfterEntityObj($this->afterEiEntry->getLiveObject());
		}
		
		if (is_object($eiEntry = $this->dispatch($addModel, 'create'))) {
			$this->redirect($this->eiuCtrl->buildRefRedirectUrl($redirectUrl, $eiEntry));
			return;
		} else if ($this->dispatch($addModel, 'createAndRepeate')) {
			$this->refresh();
			return;
		}
		
		$this->eiuCtrl->applyCommonBreadcrumbs(null, $this->getBreadcrumbLabel());
		
		$viewModel = new EntryCommandViewModel($this->eiuCtrl->frame(), null, $redirectUrl);
		$viewModel->setTitle($this->dtc->translate('ei_impl_add_title', array(
				'type' => $this->eiuCtrl->frame()->getEiFrame()->getContextEiMask()->getLabelLstr()
						->t($this->getN2nContext()->getN2nLocale()))));
		$this->forward('..\view\add.html',
				array('addModel' => $addModel, 'entryViewInfo' => $viewModel));
	}
	
	public function doDraft(ParamGet $refPath = null) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
			
		$entryForm = $this->eiuCtrl->frame()->createNewEntryForm(true);
		
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$addModel = new AddModel($eiFrame, $entryForm);
		
		if (is_object($eiEntry = $this->dispatch($addModel, 'create'))) {
			$this->redirect($this->eiuCtrl->buildRefRedirectUrl($redirectUrl, $eiEntry));
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
		} else if ($this->parentEiEntry !== null) {
			return $this->dtc->translate('ei_impl_add_child_branch_breadcrumb',
					array('parent_branch' => $eiFrameUtils->createIdentityString($this->parentEiEntry)));
		} else if ($this->beforeEiEntry !== null) {
			return$this->dtc->translate('ei_impl_add_before_branch_breadcrumb',
					array('branch' => $eiFrameUtils->createIdentityString($this->beforeEiEntry)));
		} else if ($this->afterEiEntry !== null) {
			return $this->dtc->translate('ei_impl_add_after_branch_breadcrumb',
					array('branch' => $eiFrameUtils->createIdentityString($this->afterEiEntry)));
		} else {
			return $this->dtc->translate('ei_impl_add_root_branch_breadcrumb');
		}
	}
}
