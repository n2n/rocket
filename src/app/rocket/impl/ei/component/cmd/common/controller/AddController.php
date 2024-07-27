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
namespace rocket\impl\ei\component\cmd\common\controller;

use rocket\op\OpState;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamGet;
use rocket\op\util\OpuCtrl;
use rocket\ui\si\control\SiIconType;
use rocket\ui\si\control\SiButton;
use rocket\op\ei\util\Eiu;
use rocket\core\model\Rocket;
use n2n\util\ex\IllegalStateException;
use n2n\web\http\StatusException;

class AddController extends ControllerAdapter {
	const CONTROL_SAVE_KEY = 'save';
	const CONTROL_CANCEL_KEY = 'cancel';
	
	private $dtc;
	private OpuCtrl $opuCtrl;
	
	private $parentEiuObject;
	private $beforeEiuObject;
	private $afterEiuObject;
	
	public function prepare(DynamicTextCollection $dtc, OpState $rocketState) {
		$this->dtc = $dtc;
		$this->opuCtrl = OpuCtrl::from($this->cu());
	}
		
	public function index($copyPid = null, ParamGet $refPath = null) {	
		$this->live($copyPid);
	}
	
	public function doChild($parentPid, $copyPid = null, ParamGet $refPath = null) {
		$this->parentEiuObject = $this->opuCtrl->lookupObject($parentPid);	
		$this->live($copyPid);	
	}
	
	public function doBefore($beforePid, $copyPid = null, ParamGet $refPath = null) {
		$this->beforeEiuObject = $this->opuCtrl->lookupObject($beforePid);	
		$this->live($copyPid);
	}
	
	public function doAfter($afterPid, $copyPid = null, ParamGet $refPath = null) {
		$this->afterEiuObject = $this->opuCtrl->lookupObject($afterPid);	
		$this->live($copyPid);
	}

	/**
	 * @throws StatusException
	 */
	private function live($copyPid = null) {

		$this->opuCtrl->pushOverviewBreadcrumb()
				->pushCurrentAsSirefBreadcrumb($this->dtc->t('common_add_label'));
		
		$this->opuCtrl->forwardNewBulkyEntryZone(true, true, true, $this->createControls());
	}
	
	private function createControls(): array {
		$eiuControlFactory = $this->opuCtrl->eiu()->factory()->guiControl();
		$dtc = $this->opuCtrl->eiu()->dtc(Rocket::NS);
		
		return [
				self::CONTROL_SAVE_KEY => $eiuControlFactory->newCallback(
								SiButton::primary($dtc->t('common_save_label'), SiIconType::ICON_SAVE),
								function (Eiu $eiu, array $inputEius) {
									$this->handleInput($eiu, $inputEius);
									return $eiu->factory()->newControlResponse()
											->redirectBack()
											->highlight(...array_map(function ($eiu) { return $eiu->entry(); }, $inputEius));
								})
						->setInputHandled(true),
				self::CONTROL_CANCEL_KEY => $eiuControlFactory->newCallback(
						SiButton::primary($dtc->t('common_cancel_label'), SiIconType::ICON_ARROW_LEFT),
						function (Eiu $eiu) {
							return $eiu->factory()->newControlResponse()->redirectBack();
						})
		];
	}
	
	private function handleInput(Eiu $eiu, array $inputEius) {
		foreach ($inputEius as $inputEiu) {
			$result = false;

			if ($this->parentEiuObject !== null) {
				$result = $inputEiu->entry()->insertAsChild($this->parentEiuObject);
			} else if ($this->beforeEiuObject !== null) {
				$result = $inputEiu->entry()->insertBefore($this->beforeEiuObject);
			} else if ($this->afterEiuObject !== null) {
				$result = $inputEiu->entry()->insertAfter($this->afterEiuObject);
			} else {
				$result = $inputEiu->entry()->save();
			}
			
			IllegalStateException::assertTrue($result);
		}		
	}
	
//	public function doDraft(ParamGet $refPath = null) {
//		$redirectUrl = $this->opuCtrl->parseRefUrl($refPath);
//
//		$eiuEntryForm = $this->opuCtrl->frame()->newEntryForm(true);
//
//		$eiFrame = $this->opuCtrl->frame()->getEiFrame();
//		$addModel = new AddModel($eiFrame, $eiuEntryForm);
//
//		if (is_object($eiObject = $this->dispatch($addModel, 'create'))) {
//			$this->redirect($this->opuCtrl->buildRefRedirectUrl($redirectUrl, $eiObject));
//			return;
//		}
//
//		$viewModel = new EntryCommandViewModel($this->opuCtrl->frame(), null, $redirectUrl);
//		$viewModel->setTitle($this->dtc->translate('ei_impl_add_draft_title',
//				array('type' => $this->opuCtrl->frame()->getGenericLabel())));
//		$this->forward('..\view\add.html', array('addModel' => $addModel, 'entryViewInfo' => $viewModel));
//	}
	
//	private function getBreadcrumbLabel() {
//		$eiFrameUtils = $this->opuCtrl->frame();
//
//		if (null === $eiFrameUtils->getNestedSetStrategy()) {
//			return $this->dtc->translate('ei_impl_add_breadcrumb');
//		} else if ($this->parentEiuObject !== null) {
//			return $this->dtc->translate('ei_impl_add_child_branch_breadcrumb',
//					array('parent_branch' => $eiFrameUtils->createIdentityString($this->parentEiuObject)));
//		} else if ($this->beforeEiuObject !== null) {
//			return$this->dtc->translate('ei_impl_add_before_branch_breadcrumb',
//					array('branch' => $eiFrameUtils->createIdentityString($this->beforeEiuObject)));
//		} else if ($this->afterEiuObject !== null) {
//			return $this->dtc->translate('ei_impl_add_after_branch_breadcrumb',
//					array('branch' => $eiFrameUtils->createIdentityString($this->afterEiuObject)));
//		} else {
//			return $this->dtc->translate('ei_impl_add_root_branch_breadcrumb');
//		}
//	}
}
