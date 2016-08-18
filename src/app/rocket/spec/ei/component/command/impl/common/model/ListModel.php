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
namespace rocket\spec\ei\component\command\impl\common\model;

use n2n\web\dispatch\Dispatchable;
use rocket\spec\ei\manage\EiState;
use n2n\persistence\orm\criteria\Criteria;
use rocket\spec\ei\component\field\impl\tree\TreeUtils;
use rocket\spec\ei\component\field\impl\tree\NestedSetDef;
use n2n\persistence\orm\util\NestedSetUtils;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\EntryGui;
use rocket\spec\config\mask\model\EntryGuiTree;
use rocket\spec\ei\manage\critmod\impl\model\CritmodForm;
use rocket\spec\ei\manage\critmod\quick\impl\form\QuickSearchForm;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\util\NestedSetStrategy;

class ListModel implements Dispatchable {	
	private $utils;
	private $listSize;
	
	private $currentPageNo;
	private $numPages;
	private $numEntries;
	
	private $entryGuis;
	private $entryGuiTree;
		
	private $critmodForm;
	private $quickSearchForm;
	
	public function __construct(EiState $eiState, int $listSize, CritmodForm $critmodForm, 
			QuickSearchForm $quickSearchForm) {
		$this->utils = new EiStateUtils($eiState);
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
	
	public function getEiState(): EiState {
		return $this->utils->getEiState();
	}
	
// 	public function emptyInitialize() {
// 		$eiState = $this->getEiState();
		
// 		$this->critmodForm->applyToEiState($eiState, true);
// 		$this->quickSearchForm->applyToEiState($eiState, true);
		
// 		$countCriteria = $eiState->createCriteria('o');
// 		$countCriteria->select('COUNT(o)');
// 		$this->numEntries = $countCriteria->toQuery()->fetchSingle();
// 		$this->numPages = ceil($this->numEntries / $this->listSize);
// 		$this->entryGuis = array();
// 	}
	
	public function initialize($pageNo): bool {
		if (!is_numeric($pageNo) || $pageNo < 1) return false;
		
		$eiState = $this->getEiState();

		$this->critmodForm->applyToEiState($eiState, true);
		$this->quickSearchForm->applyToEiState($eiState, true);
		
		$countCriteria = $eiState->createCriteria('o');
		$countCriteria->select('COUNT(o)');
		$this->numEntries = $countCriteria->toQuery()->fetchSingle();
		
		$this->currentPageNo = $pageNo;
		$limit = ($pageNo - 1) * $this->listSize;
		if ($limit > $this->numEntries) {
			return false;
		}
		$this->numPages = ceil($this->numEntries / $this->listSize);
		if (!$this->numPages) $this->numPages = 1;
		
		$criteria = $eiState->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit, $this->listSize);
		
		if (null !== ($nestedSetStrategy = $eiState->getContextEiMask()->getEiEngine()->getEiSpec()
				->getNestedSetStrategy())) {
			$this->treeLookup($criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($criteria);
		}
				
		return true;
	}
	
	public function initByIdReps(array $idReps) {
		$eiState = $this->getEiState();
				
		$eiSpec = $eiState->getContextEiMask()->getEiEngine()->getEiSpec();
		$ids = array();
		foreach ($idReps as $idRep) {
			$ids[] = $eiSpec->idRepToId($idRep);
		}
	
		$criteria = $eiState->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)
			->where()->match(CrIt::p(NestedSetUtils::NODE_ALIAS, $eiSpec->getEntityModel()->getIdDef()->getEntityProperty()), 'IN', $idReps);
		
		if (null !== ($nestedSetStrategy = $eiSpec->getNestedSetStrategy())) {
			$this->treeLookup($criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($criteria);
		}
		
		return true;
	}
	
	private function simpleLookup(Criteria $criteria) {
		$this->entryGuis = array();
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiMapping = $this->utils->createEiMapping($this->utils->createEiSelectionFromLiveEntry($entityObj));
			$this->entryGuis[$eiMapping->getIdRep()] = new EntryGui($this->utils->getEiMask()
					->createListEntryGuiModel($this->utils->getEiState(), $eiMapping, false)); 
		}
	}
	
	private function treeLookup(Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->utils->em(), $this->utils->getClass(), $nestedSetStrategy);
		
		$eiState = $this->utils->getEiState();
		$eiMask = $this->utils->getEiMask();
		
		$this->entryGuiTree = new EntryGuiTree();
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiMapping = $this->utils->createEiMapping(
					$this->utils->createEiSelectionFromLiveEntry($nestedSetItem->getEntityObj()));
			
			$this->entryGuiTree->addByLevel($nestedSetItem->getLevel(), new EntryGui(
					$eiMask->createTreeEntryGuiModel($eiState, $eiMapping, false)));
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
	
	public function isTree(): bool {
		return $this->entryGuiTree !== null;
	}
	
	public function getEntryGuis(): array {
		if ($this->entryGuis !== null) {
			return $this->entryGuis;
		}
		
		throw new IllegalStateException();
	}
	
	public function getEntryGuiTree(): EntryGuiTree {
		if ($this->entryGuiTree !== null) {
			return $this->entryGuiTree;
		}
	
		throw new IllegalStateException();
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
			if (!isset($this->eiSelections[$entryId])) continue;
			
			$selectedObjects[$entryId] = $this->eiSelections[$entryId];
		}
		
		if (!sizeof($selectedObjects)) return;
		
		$executedEiCommand->processEntries($this->eiState, $selectedObjects);
	}
}
