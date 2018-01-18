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
namespace rocket\impl\ei\component\command\common\model;

use n2n\web\dispatch\Dispatchable;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\util\NestedSetUtils;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\critmod\impl\model\CritmodForm;
use rocket\spec\ei\manage\critmod\quick\impl\form\QuickSearchForm;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\spec\ei\manage\gui\ViewMode;

class OverviewModel implements Dispatchable {	
	private $eiuFrame;
	private $listSize;
	
	private $currentPageNo;
	private $numPages;
	private $numEntries;
	
	private $eiuGui;
		
	private $critmodForm;
	private $quickSearchForm;
	
	public function __construct(EiuFrame $eiuFrame, int $listSize, CritmodForm $critmodForm, 
			QuickSearchForm $quickSearchForm) {
		$this->eiuFrame = $eiuFrame;
		$this->listSize = $listSize;
		$this->critmodForm = $critmodForm;
		$this->quickSearchForm = $quickSearchForm;
	}
	
	public function getCritmodForm(): CritmodForm {
		return $this->critmodForm;
	}
	
	public function getQuickSearchForm(): QuickSearchForm {
		return $this->quickSearchForm;
	}
	
	public function getEiuFrame() {
		return $this->eiuFrame;
	}
	
// 	public function emptyInitialize() {
// 		$eiFrame = $this->getEiFrame();
		
// 		$this->critmodForm->applyToEiFrame($eiFrame, true);
// 		$this->quickSearchForm->applyToEiFrame($eiFrame, true);
		
// 		$countCriteria = $eiFrame->createCriteria('o');
// 		$countCriteria->select('COUNT(o)');
// 		$this->numEntries = $countCriteria->toQuery()->fetchSingle();
// 		$this->numPages = ceil($this->numEntries / $this->listSize);
// 		$this->entryGuis = array();
// 	}
	
	public function initialize(int $pageNo, int $numPages = 1): bool {
		if (!is_numeric($pageNo) || $pageNo < 1) return false;
		
		$eiFrame = $this->getEiuFrame()->getEiFrame();

		$this->critmodForm->applyToEiFrame($eiFrame, true);
		$this->quickSearchForm->applyToEiFrame($eiFrame, true);
		
		$countCriteria = $eiFrame->createCriteria('o');
		$countCriteria->select('COUNT(o)');
		$this->numEntries = $countCriteria->toQuery()->fetchSingle();
		
		$this->currentPageNo = $pageNo;
		$limit = ($pageNo - 1) * $this->listSize;
		if ($limit > $this->numEntries) {
			return false;
		}
		$this->numPages = ceil($this->numEntries / $this->listSize);
		if (!$this->numPages) $this->numPages = 1;
		
		$criteria = $eiFrame->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit, ($this->listSize * $numPages));
		
		if (null !== ($nestedSetStrategy = $eiFrame->getContextEiMask()->getEiEngine()->getEiType()
				->getNestedSetStrategy())) {
			$this->treeLookup($criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($criteria);
		}
				
		return true;
	}
	
	public function initByIdReps(array $idReps) {
		$eiFrame = $this->getEiuFrame()->getEiFrame();
		
		$this->critmodForm->applyToEiFrame($eiFrame, true);
		$this->quickSearchForm->applyToEiFrame($eiFrame, true);
				
		$eiType = $eiFrame->getContextEiMask()->getEiEngine()->getEiType();
		$ids = array();
		foreach ($idReps as $idRep) {
			$ids[] = $eiType->idRepToId($idRep);
		}
	
		$criteria = $eiFrame->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)
				->where()->match(CrIt::p(NestedSetUtils::NODE_ALIAS, $eiType->getEntityModel()->getIdDef()->getEntityProperty()), 'IN', $ids);
		
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($criteria);
		}
		
		return true;
	}
	
	private function simpleLookup(Criteria $criteria) {
		$this->eiuGui = $this->eiuFrame->newGui(ViewMode::COMPACT_READ)->allowControls();
		
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$this->eiuGui->appendNewEntryGui($entityObj);
		}
	}
	
	private function treeLookup(Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiuFrame->em(), $this->eiuFrame->getClass(), $nestedSetStrategy);
		
		$this->eiuGui = $this->eiuFrame->newGui(ViewMode::COMPACT_READ)->allowControls();
		
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$this->eiuGui->appendNewEntryGui($nestedSetItem->getEntityObj(), $nestedSetItem->getLevel());
		}
	}
	
	public function getNumPages() {
		return $this->numPages;
	}
	
	public function getCurrentPageNo() {
		return $this->currentPageNo;
	}
	
	public function getNumEntries() {
		return $this->numEntries;
	}
	
	public function getPageSize() {
	    return $this->listSize;
	}
		
	/**
	 * 
	 * @return \rocket\spec\ei\manage\util\model\EiuGui
	 */
	public function getEiuGui() {
		return $this->eiuGui;
	}
	
	protected $selectedObjectIds = array();
	protected $executedPartialCommandKey = null;
	
	public function getSelectedObjectIds() {
		return $this->selectedObjectIds;
	}
	
	public function setSelectedObjectIds(array $selectedObjectIds) {
		$this->selectedObjectIds = $selectedObjectIds;
	}
	
	public function getExecutedPartialCommandKey() {
		return $this->executedPartialCommandKey;
	}
	
	public function setExecutedPartialCommandKey($executedPartialCommandKey) {
		$this->executedPartialCommandKey = $executedPartialCommandKey;
	}
	
	private function _validation() {}
	
	public function executePartialCommand() {
		$executedEiCommand = null;
		if (isset($this->partialEiCommands[$this->executedPartialCommandKey])) {
			$executedEiCommand = $this->partialEiCommands[$this->executedPartialCommandKey];
		}
		
		$selectedObjects = array();
		foreach ($this->selectedObjectIds as $entryId) {
			if (!isset($this->eiObjects[$entryId])) continue;
			
			$selectedObjects[$entryId] = $this->eiObjects[$entryId];
		}
		
		if (!sizeof($selectedObjects)) return;
		
		$executedEiCommand->processEntries($this->eiFrame, $selectedObjects);
	}
}
