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

use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\component\command\impl\common\model\ListModel;
use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\manage\critmod\impl\model\CritmodSaveDao;
use rocket\spec\ei\manage\critmod\impl\model\CritmodForm;
use rocket\spec\ei\manage\critmod\quick\impl\form\QuickSearchForm;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\spec\ei\manage\critmod\filter\impl\controller\FilterFieldController;
use n2n\web\http\controller\ParamQuery;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\component\command\impl\common\model\DraftListModel;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class OverviewController extends ControllerAdapter {
	private $listSize;
// 	private $manageState;
// 	private $rocketState;
	private $scrRegistry;
	
	private $eiuCtrl;
	
	public function __construct(int $listSize) {
		$this->listSize = $listSize;
	}
	
	public function prepare(/*ManageState $manageState, RocketState $rocketState,*/ ScrRegistry $scrRegistry) {
// 		$this->manageState = $manageState;
// 		$this->rocketState = $rocketState;
		$this->scrRegistry = $scrRegistry;
		$this->eiuCtrl = EiuCtrl::from($this->getHttpContext());
	}
	
	public function index(CritmodSaveDao $critmodSaveDao, $pageNo = null) {
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$stateKey = OverviewAjahController::genStateKey();
		$critmodForm = CritmodForm::create($eiFrame, $critmodSaveDao, $stateKey);
		$quickSearchForm = QuickSearchForm::create($eiFrame, $critmodSaveDao, $stateKey);
		$listModel = new ListModel($eiFrame, $this->listSize, $critmodForm, $quickSearchForm);
		
		if ($pageNo === null) {
			$pageNo = 1;
		} else if ($pageNo == 1) {
			throw new PageNotFoundException();
		}
		
		if (!$listModel->initialize($pageNo)) {
			throw new PageNotFoundException();
		}
		
		$listView = null;
		if ($listModel->isTree()) {
			$listView = $eiFrame->getContextEiMask()->createTreeView($eiFrame, $listModel->getEntryGuiTree());
		} else {
			$listView = $eiFrame->getContextEiMask()->createListView($eiFrame, $listModel->getEntryGuis());
		}

		$overviewAjahHook = OverviewAjahController::buildAjahHook($this->getHttpContext()->getControllerContextPath(
				$this->getControllerContext())->ext('ajah')->toUrl(), $stateKey);
		$filterAjahHook = FilterFieldController::buildFilterAjahHook($this->getHttpContext()
				->getControllerContextPath($this->getControllerContext())->ext('filter')->toUrl());
		
		$this->eiuCtrl->applyCommonBreadcrumbs();
		
		$this->forward('..\view\overview.html', 
				array('listModel' => $listModel, 'critmodForm' => $critmodForm,
						'quickSearchForm' => $quickSearchForm, 'overviewAjahHook' => $overviewAjahHook, 
						'filterAjahHook' => $filterAjahHook, 'listView' => $listView));
	}
	
	public function doAjah(array $delegateCmds = array(), OverviewAjahController $ajahOverviewController, 
			ParamQuery $pageNo = null) {
		if ($pageNo !== null) {
			$pageNo = $pageNo->toNumericOrReject();
			$this->eiuCtrl->frame()->getEiFrame()->setCurrentUrlExt(
					$this->getControllerContext()->getCmdContextPath()->ext($pageNo > 1 ? $pageNo : null)->toUrl());
		}
				
		$this->delegate($ajahOverviewController);
	}
	
	public function doFilter(array $delegateCmds = array(), FilterFieldController $filterFieldController) {
		$this->delegate($filterFieldController);
	}
	
	public function doDrafts($pageNo = null, DynamicTextCollection $dtc) {
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$draftListModel = new DraftListModel($eiFrame, $this->listSize);
		
		if ($pageNo === null) {
			$pageNo = 1;
		} else if ($pageNo == 1) {
			throw new PageNotFoundException();
		}
		
		if (!$draftListModel->initialize($pageNo)) {
			throw new PageNotFoundException();
		}
		
		$listView = $eiFrame->getContextEiMask()->createListView($eiFrame, $draftListModel->getEntryGuis());
		
		$this->eiuCtrl->applyCommonBreadcrumbs(null, $dtc->translate('ei_impl_drafts_title'));
		
		$stateKey = OverviewDraftAjahController::genStateKey();
		$overviewDraftAjahHook = OverviewDraftAjahController::buildAjahHook($this->getHttpContext()->getControllerContextPath(
				$this->getControllerContext())->ext('draftAjah')->toUrl(), $stateKey);

		$this->forward('..\view\overviewDrafts.html', array('draftListModel' => $draftListModel, 
				'overviewDraftAjahHook' => $overviewDraftAjahHook, 'listView' => $listView));
	}

	public function doDraftAjah(array $delegateCmds = array(), OverviewDraftAjahController $overviewDraftAjahController,
			ParamQuery $pageNo = null) {
		if ($pageNo !== null) {
			$this->eiuCtrl->frame()->getEiFrame()->setCurrentUrlExt(
					$this->getControllerContext()->getCmdContextPath()->ext('drafts', $pageNo->toNumericOrReject())->toUrl());
		}

		$this->delegate($overviewDraftAjahController);
	}
	
	public function doDelete($pageNo = null) {
		$eiFrame = $this->manageState->peakEiFrame();
		
// 		$this->manageState->getDraftManager()->findRemoved();
	}
	
	
// 	private function createNavPoints(ListModel $listModel) {
// 		if ($listModel->getNumPages() < 2) return array();
		
// 		$request = $this->getRequest();
// 		$navPoints = array();
// 		for ($pageNo = 1; $pageNo <= $listModel->getNumPages(); $pageNo++) {
// 			$navPoints[$request->getControllerContextPath($this->getControllerContext(), ($pageNo > 1 ? $pageNo : null))] = $pageNo;
// 		}
// 		return $navPoints;
// 	}
}
