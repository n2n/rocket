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

class OverviewController extends ControllerAdapter {
	private $listSize;
// 	private $manageState;
// 	private $rocketState;
	private $scrRegistry;
	
	private $eiCtrlUtils;
	
	public function __construct(int $listSize) {
		$this->listSize = $listSize;
	}
	
	public function prepare(/*ManageState $manageState, RocketState $rocketState,*/ ScrRegistry $scrRegistry) {
// 		$this->manageState = $manageState;
// 		$this->rocketState = $rocketState;
		$this->scrRegistry = $scrRegistry;
		$this->eiCtrlUtils = EiCtrlUtils::from($this->getHttpContext());
	}
	
	public function index(CritmodSaveDao $critmodSaveDao, $pageNo = null) {
		$eiState = $this->eiCtrlUtils->getEiState();
		$stateKey = OverviewAjahController::genStateKey();
		$critmodForm = CritmodForm::create($eiState, $critmodSaveDao, $stateKey);
		$quickSearchForm = QuickSearchForm::create($eiState, $critmodSaveDao, $stateKey);
		$listModel = new ListModel($eiState, $this->listSize, $critmodForm, $quickSearchForm);
		
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
			$listView = $eiState->getContextEiMask()->createTreeView($eiState, $listModel->getEntryGuiTree());
		} else {
			$listView = $eiState->getContextEiMask()->createListView($eiState, $listModel->getEntryGuis());
		}

		$overviewAjahHook = OverviewAjahController::buildAjahHook($this->getHttpContext()->getControllerContextPath(
				$this->getControllerContext())->ext('ajah')->toUrl(), $stateKey);
		$filterAjahHook = FilterFieldController::buildFilterAjahHook($this->getHttpContext()
				->getControllerContextPath($this->getControllerContext())->ext('filter')->toUrl());
		
		$this->eiCtrlUtils->applyCommonBreadcrumbs();
		
		$this->forward('..\view\overview.html', 
				array('listModel' => $listModel, 'critmodForm' => $critmodForm,
						'quickSearchForm' => $quickSearchForm, 'overviewAjahHook' => $overviewAjahHook, 
						'filterAjahHook' => $filterAjahHook, 'listView' => $listView));
	}
	
	public function doAjah(array $delegateCmds = array(), OverviewAjahController $ajahOverviewController, 
			ParamQuery $pageNo = null) {
		if ($pageNo !== null) {
			$pageNo = $pageNo->toNumericOrReject();
			$this->eiCtrlUtils->getEiState()->setCurrentUrlExt(
					$this->getControllerContext()->getCmdContextPath()->ext($pageNo > 1 ? $pageNo : null)->toUrl());
		}
				
		$this->delegate($ajahOverviewController);
	}
	
	public function doFilter(array $delegateCmds = array(), FilterFieldController $filterFieldController) {
		$this->delegate($filterFieldController);
	}
	
	public function doDrafts($pageNo = null, DynamicTextCollection $dtc) {
		$eiState = $this->eiCtrlUtils->getEiState();
		$draftListModel = new DraftListModel($eiState, $this->listSize);
		
		if ($pageNo === null) {
			$pageNo = 1;
		} else if ($pageNo == 1) {
			throw new PageNotFoundException();
		}
		
		if (!$draftListModel->initialize($pageNo)) {
			throw new PageNotFoundException();
		}
		
		$listView = $eiState->getContextEiMask()->createListView($eiState, $draftListModel->getEntryGuis());
		
		$this->eiCtrlUtils->applyCommonBreadcrumbs(null, $dtc->translate('ei_impl_drafts_title'));
		
		$stateKey = OverviewDraftAjahController::genStateKey();
		$overviewDraftAjahHook = OverviewDraftAjahController::buildAjahHook($this->getHttpContext()->getControllerContextPath(
				$this->getControllerContext())->ext('draftAjah')->toUrl(), $stateKey);

		$this->forward('..\view\overviewDrafts.html', array('draftListModel' => $draftListModel, 
				'overviewDraftAjahHook' => $overviewDraftAjahHook, 'listView' => $listView));
	}
	

	public function doDraftAjah(array $delegateCmds = array(), OverviewDraftAjahController $overviewDraftAjahController,
			ParamQuery $pageNo = null) {
		if ($pageNo !== null) {
			$this->eiCtrlUtils->getEiState()->setCurrentUrlExt(
					$this->getControllerContext()->getCmdContextPath()->ext('drafts', $pageNo->toNumericOrReject())->toUrl());
		}

		$this->delegate($overviewDraftAjahController);
	}
	
	public function doDelete($pageNo = null) {
		$eiState = $this->manageState->peakEiState();
		
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
