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

use n2n\web\http\controller\ControllerAdapter;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\critmod\impl\model\CritmodSaveDao;
use n2n\web\http\controller\impl\ScrRegistry;
use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\manage\critmod\filter\impl\controller\GlobalFilterFieldController;
use n2n\impl\web\ui\view\html\AjahResponse;
use n2n\web\http\controller\ParamQuery;
use rocket\spec\ei\manage\critmod\impl\model\CritmodForm;
use rocket\spec\ei\manage\critmod\quick\impl\form\QuickSearchForm;
use rocket\spec\ei\component\command\impl\common\model\OverviewModel;
use n2n\util\uri\Url;
use rocket\core\model\Rocket;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\util\model\EiuCtrl;

class OverviewAjahController extends ControllerAdapter {
	private $manageState;
	private $eiuCtrl;
	private $critmodSaveDao;
	private $listSize = 30;

	public function setListSize(int $listSize) {
		$this->listSize = $listSize;
	}

	public function getListSize(): int {
		return $this->listSize;
	}

	public function prepare(ManageState $manageState, CritmodSaveDao $critmodSaveDao, EiuCtrl $eiuCtrl) {
		$this->manageState = $manageState;
		$this->critmodSaveDao = $critmodSaveDao;
		$this->eiuCtrl = $eiuCtrl;
	}

	public function doOverviewTools(string $stateKey, ScrRegistry $scrRegistry) {
		$eiFrame = $this->manageState->peakEiFrame();
		
		$critmodForm = CritmodForm::create($eiFrame, $this->critmodSaveDao, $stateKey);
		$quickSearchForm = QuickSearchForm::create($eiFrame, $this->critmodSaveDao, $stateKey);
		
		$overviewAjahHook = OverviewAjahController::buildAjahHook($this->getHttpContext()->getControllerContextPath(
				$this->getControllerContext())->toUrl(), $stateKey);
		$filterAjahHook = GlobalFilterFieldController::buildFilterAjahHook($scrRegistry, $eiFrame->getContextEiMask());
		$listModel = new OverviewModel($this->eiuCtrl->frame(), $this->listSize, $critmodForm, $quickSearchForm);
		
		if (!$listModel->initialize(1)) {
			throw new PageNotFoundException();
		}
		
		$overView = $listModel->getEiuGui()->createView();
		$eiUtils = new EiuFrame($eiFrame);
		
		$this->send(new AjahResponse($this->createView(
				'..\view\ajahOverview.html',
				array('critmodForm' => $critmodForm, 'quickSearchForm' => $quickSearchForm, 
						'overviewAjahHook' => $overviewAjahHook, 'filterAjahHook' => $filterAjahHook,
						'label' => $eiUtils->getGenericLabel(), 'pluralLabel' => $eiUtils->getGenericPluralLabel(),
						'listView' => $overView, 'listModel' => $listModel))));
	}
	
	
	public function doCritmodForm(string $stateKey, ScrRegistry $scrRegistry) {
		$eiFrame = $this->manageState->peakEiFrame();

		$critmodForm = CritmodForm::create($eiFrame, $this->critmodSaveDao, $stateKey);

		$valid = false;
		if ($this->dispatch($critmodForm, 'select') || $this->dispatch($critmodForm, 'apply')
				|| $this->dispatch($critmodForm, 'clear') || $this->dispatch($critmodForm, 'save')
				|| $this->dispatch($critmodForm, 'saveAs') || $this->dispatch($critmodForm, 'delete')) {
			$valid = true;
// 			$this->refresh();
// 			return;
		}
		
		$eiMask = $eiFrame->getContextEiMask();
		$filterAjahHook = GlobalFilterFieldController::buildFilterAjahHook($scrRegistry, $eiMask);
		
// 		$this->forward('spec\ei\manage\critmod\impl\view\critmodForm.html',
// 				array('critmodForm' => $critmodForm, 'critmodFormUrl' => $this->getRequest()->getUrl(),
// 						'filterAjahHook' => $filterAjahHook));
		
		$this->send(new AjahResponse($this->createView('~\spec\ei\manage\critmod\impl\view\critmodForm.html',
				array('critmodForm' => $critmodForm, 'critmodFormUrl' => $this->getRequest()->getUrl(),
						'filterAjahHook' => $filterAjahHook)),
				array('valid' => $valid)));
				
	}
	
// 	public function doCritmodForm(ParamGet $selectedSaveId = null, ScrRegistry $scrRegistry) {
// 		$eiFrame = $this->manageState->peakEiFrame();

// 		$filterGroupData = null;
// 		$sortData = null;
// 		if ($selectedSaveId === null) {
// 			$filterGroupData = new FilterGroupData();
// 			$sortData = new SortData();
// 		} else {
// 			$critmodSave = $this->critmodSaveDao->getCritmodSaveById((string) $selectedSaveId);
// 			if (null === $critmodSave) {
// 				throw new PageNotFoundException();
// 			}
				
// 			$filterGroupData = $critmodSave->readFilterGroupData();
// 			$sortData = $critmodSave->readSortData();
// 		}

// 		$eiMask = $eiFrame->getContextEiMask();
// 		$filterGroupForm = new FilterGroupForm($filterGroupData, $eiMask->createManagedFilterDefinition($eiFrame));
// 		$filterAjahHook = GlobalFilterFieldController::buildFilterAjahHook($scrRegistry, $eiMask);
// 		$sortForm = new SortForm($sortData, $eiMask->createManagedSortDefinition($eiFrame));

// 		$this->send(new AjahResponse($this->createView(
// 				'spec\ei\component\command\impl\common\view\pseudoCritmodForm.html',
// 				array('filterGroupForm' => $filterGroupForm, 'filterAjahHook' => $filterAjahHook,
// 						'sortForm' => $sortForm))));
// 	}

	public function doSelect(string $stateKey, ParamQuery $pageNo = null, ParamQuery $idReps = null) {
		$eiFrame = $this->manageState->peakEiFrame();

		$critmodForm = CritmodForm::create($eiFrame, $this->critmodSaveDao, $stateKey);
		$quickSearchForm = QuickSearchForm::create($eiFrame, $this->critmodSaveDao, $stateKey);
		$listModel = new OverviewModel($this->eiuCtrl->frame(), $this->listSize, $critmodForm, $quickSearchForm);

		if ($idReps != null) {
			$listModel->initByIdReps($idReps->toStringArrayOrReject());
		} else {
			if ($pageNo === null) {
				throw new PageNotFoundException();
			}
			
			if ($this->dispatch($quickSearchForm, 'search') || $this->dispatch($quickSearchForm, 'clear')) {
				//
			}
				
			if (!$listModel->initialize($pageNo->toNumericOrReject())) {
				throw new PageNotFoundException();
			}
		}
		
		$attrs = array('numEntries' => $listModel->getNumEntries(), 'numPages' => $listModel->getNumPages());

		$this->send(new AjahResponse($listModel->getEiuGui()->createView(), $attrs));
	}

	public static function buildToolsAjahUrl(Url $contextUrl): Url {
		return $contextUrl->extR(array('overviewtools', self::genStateKey()));
	}
	
	public static function buildAjahHook(Url $contextUrl, string $stateKey) {
		return new OverviewAjahHook($stateKey, $contextUrl->extR(array('critmodform', $stateKey)),
				$contextUrl->extR(array('select', $stateKey)));
	}

	public static function genStateKey() : string {
		return uniqid();
	}
}

class OverviewAjahHook {
	private $stateKey;
	private $critmodFormUrl;
	private $selectUrl;

	public function __construct(string $stateKey, Url $critmodFormUrl, Url $selectUrl) {
		$this->stateKey = $stateKey;
		$this->critmodFormUrl = $critmodFormUrl;
		$this->selectUrl = $selectUrl;
	}

	public function getStateKey(): string {
		return $this->stateKey;
	}

	public function getCritmodFormUrl(): Url {
		return $this->critmodFormUrl;
	}

	public function getSelectUrl(): Url {
		return $this->selectUrl;
	}
}
