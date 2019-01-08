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

use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\manage\ManageState;
use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ParamQuery;
use n2n\util\uri\Url;
use rocket\impl\ei\component\command\common\model\DraftListModel;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;

class OverviewDraftJhtmlController extends ControllerAdapter {
	private $manageState;
	private $critmodSaveDao;
	private $listSize = 30;

	public function setListSize(int $listSize) {
		$this->listSize = $listSize;
	}

	public function getListSize(): int {
		return $this->listSize;
	}

	public function prepare(ManageState $manageState) {
		$this->manageState = $manageState;
	}
	
	public function doSelect(string $stateKey, ParamQuery $pageNo, ParamQuery $pids = null) {
		$eiFrame = $this->manageState->peakEiFrame();

		$draftListModel = new DraftListModel($eiFrame, $this->listSize);
		
		if ($pids != null) {
			$draftListModel->initByPids($pids->toStringArrayOrReject());
		} else {
			if ($pageNo === null) {
				throw new PageNotFoundException();
			}
				
			if (!$draftListModel->initialize($pageNo->toNumericOrReject())) {
				throw new PageNotFoundException();
			}
		}
		
		$attrs = array('numEntries' => $draftListModel->getNumEntries(), 'numPages' => $draftListModel->getNumPages());

		$this->send(JhtmlResponse::view($eiFrame->getContextEiEngine()->getEiMask()->createListView($eiFrame,
				$draftListModel->getEntryGuis()), $attrs));
	}

	
	public static function buildAjahHook(Url $contextUrl, string $stateKey) {
		return new OverviewDraftAjahHook($stateKey, $contextUrl->extR(array('critmodform', $stateKey)),
				$contextUrl->extR(array('select', $stateKey)));
	}

	public static function genStateKey() : string {
		return uniqid();
	}
}

class OverviewDraftAjahHook {
	private $stateKey;
	private $selectUrl;

	public function __construct(string $stateKey, Url $selectUrl) {
		$this->stateKey = $stateKey;
		$this->selectUrl = $selectUrl;
	}

	public function getStateKey(): string {
		return $this->stateKey;
	}

	public function getSelectUrl(): Url {
		return $this->selectUrl;
	}
}
