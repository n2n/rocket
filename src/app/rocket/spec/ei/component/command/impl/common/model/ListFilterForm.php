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

use rocket\spec\ei\manage\critmod\FilterStore;
use n2n\dispatch\val\ValIsset;
use n2n\dispatch\map\val\impl\ValEnum;
use n2n\dispatch\map\BindingConstraints;
use n2n\reflection\annotation\AnnoInit;
use n2n\dispatch\Dispatchable;
use rocket\spec\ei\manage\EiState;
use n2n\dispatch\map\bind\BindingErrors;
use n2n\l10n\MessageCode;
use n2n\l10n\DynamicTextCollection;
use n2n\l10n\MessageContainer;
use rocket\spec\ei\manage\critmod\FilterForm;
use rocket\spec\ei\manage\critmod\sort\impl\form\SortForm;
use n2n\dispatch\annotation\AnnoDispObject;

class ListFilterForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('filterForm', new AnnoDispObject());
		$ai->p('sortForm', new AnnoDispObject());
	}
	
	private $dtc;
	private $eiState;
	
	private $filterStore;
	private $tmpFilterStore;
	private $filters = array();
	private $filterModel;
	private $sortModel;
	private $active = false;
	
	protected $selectedFilterId;
	protected $filterForm;
	protected $sortForm;
	protected $newFilterName;
	
	public function __construct(EiState $eiState, FilterStore $filterStore, ListTmpFilterStore $tmpFilterStore) {
		$this->dtc = new DynamicTextCollection('rocket', $eiState->getN2nLocale());
		$this->eiState = $eiState;
		$this->filterStore = $filterStore;
		$this->tmpFilterStore = $tmpFilterStore;

		$this->filterModel = $eiState->getOrCreateFilterModel();
		$this->filterForm = FilterForm::createFromFilterModel($this->filterModel);
		$this->sortModel = $eiState->getOrCreateSortModel();
		$this->sortForm = SortForm::createFromSortModel($this->sortModel);
		
		$contextId = $eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		
		foreach ($this->filterStore->getFiltersById($contextId) as $filter) {
			$this->filters[$filter->getId()] = $filter;
		}
		
		$this->selectedFilterId = $tmpFilterStore->getFilterId($contextId);
		
		if (null !== ($tmpFilterData = $tmpFilterStore->getTmpFilterData($contextId))) {
			$this->filterForm->writeFilterData($tmpFilterData);
		}
		
		if (null !== ($sortDirections = $tmpFilterStore->getTmpSortDirections($contextId))) {
			$this->sortForm->setSortDirections($sortDirections);
		}
	}
	
	public function applyToEiState(EiState $eiState) {
		$filterCriteriaConstraint = $this->filterModel->createCriteriaConstraint(
				$this->filterForm->readFilterData());
		if ($filterCriteriaConstraint !== null) {
			$eiState->addCriteriaConstraint($filterCriteriaConstraint);
		}
		
		$sortDirections = $this->sortForm->getSortDirections();
		if (empty($sortDirections)) {
			$sortDirections = $this->eiState->getContextEiMask()->getDefaultSortData();
		}
		
		$sortCriteriaConstraint = $this->sortModel->createCriteriaConstraint($sortDirections);
		if ($sortCriteriaConstraint !== null) {
			$eiState->addCriteriaConstraint($sortCriteriaConstraint);
		}
	}	
	
	public function getScriptId() {
		return $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
	}
	
	public function isActive() {
		return $this->active;
	}
	
	public function hasSelectedFilter() {
		return isset($this->selectedFilterId);
	}
		
	public function getSelectedFilterId() {
		return $this->selectedFilterId;
	}
	
	public function setSelectedFilterId($selectedFilterDataId) {
		$this->selectedFilterId = $selectedFilterDataId;
	}
	
	public function getSelectedFilterIdOptions() {	
		$filterIdOptions = array(null => null);
		
		if ($this->isActive() && $this->selectedFilterId === null) {
			$filterIdOptions[null] = $this->dtc->translate('ei_impl_list_unsaved_filter_label');
		}
		
		foreach ($this->filters as $filterData) {
			$filterIdOptions[$filterData->getId()] = $filterData->getName();
		}
		
		return $filterIdOptions;
	}
		
	public function setNewFilterName($newFilterName) {
		$this->newFilterName = $newFilterName;
	}
	
	public function getNewFilterName() {
		return $this->newFilterName;
	}
	
	public function setFilterForm(FilterForm $filterForm) {
		$this->filterForm = $filterForm;
	}
	
	public function getFilterForm() {
		return $this->filterForm;
	}
	
	public function setSortForm(SortForm $sortForm) {
		$this->sortForm = $sortForm;
	} 
	
	public function getSortForm() {
		return $this->sortForm;
	}
	
	public function getSortableEiFieldIds() {
		return array_keys($this->sortableEiFields);
	}
	
	private function _validation(BindingConstraints $bc) {
		if ('createFilter' == $bc->getMethodName()) {
			$bc->val('newFilterName', new ValIsset());
			
			$filterStore = $this->filterStore;
			$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
			$bc->addClosureValidator(function ($newFilterName, BindingErrors $be) use ($filterStore, $eiSpecId, $bc) {
				if ($filterStore->containsFilterName($eiSpecId, $newFilterName)) {
					$be->addError('newFilterName', new MessageCode('ei_impl_list_filter_name_already_exists'));
				}
			});
			
		} else {
			$bc->val('selectedFilterId', new ValEnum(array_keys($this->getSelectedFilterIdOptions()), false));
		}
	}
	
	public function selectFilter() {
		$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		
		$filterData = null;
		$sortDirections = null;
		if (isset($this->filters[$this->selectedFilterId])) {
			$filter = $this->filters[$this->selectedFilterId];
			$filterData = $filter->readFilterData();
			$sortDirections = $filter->getSortDirections();
		}
		
		$this->tmpFilterStore->setFilterId($eiSpecId, $this->selectedFilterId);
		$this->tmpFilterStore->setTmpFilterData($eiSpecId, $filterData);
		$this->tmpFilterStore->setTmpSortDirections($eiSpecId, $sortDirections);
	}
	
	public function apply() {
		$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		$this->tmpFilterStore->setFilterId($eiSpecId, null);
		$this->tmpFilterStore->setTmpFilterData($eiSpecId, 
				$this->filterForm->readFilterData());
		$this->tmpFilterStore->setTmpSortDirections($eiSpecId, 
				$this->sortForm->getSortDirections());
	}
	
	public function clear() {
		$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		$this->tmpFilterStore->setFilterId($eiSpecId, null);
		$this->tmpFilterStore->setTmpFilterData($eiSpecId, null);
		$this->tmpFilterStore->setTmpSortDirections($eiSpecId, null);
	}
	
	public function saveFilter(MessageContainer $mc) {
		if (null === $this->selectedFilterId || !isset($this->filters[$this->selectedFilterId])) return;
		
		$filter = $this->filters[$this->selectedFilterId];
		
		$filterData = $this->filterForm->readFilterData();
		$filter->writeFilterData($filterData);
		
		$sortDirections = $this->sortForm->getSortDirections();
		$filter->setSortDirections($sortDirections);
	
		$filter = $this->filterStore->mergeFilter($filter);
		
		$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		$this->tmpFilterStore->setFilterId($eiSpecId, $this->selectedFilterId);
		$this->tmpFilterStore->setTmpFilterData($eiSpecId, $filterData);
		$this->tmpFilterStore->setTmpSortDirections($eiSpecId, $sortDirections);
		
		$mc->addInfoCode('spec_filter_saved_info', array('name' => $filter->getName()));
	}
	
	public function createFilter(MessageContainer $mc) {
		$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
				
		$filterData = $this->filterForm->readFilterData();
		$sortDirections = $this->sortForm->getSortDirections();
		
		$filter = $this->filterStore->createFilter($eiSpecId, $this->newFilterName, 
				$filterData, $sortDirections);

		$this->tmpFilterStore->setFilterId($eiSpecId, $filter->getId());
		$this->tmpFilterStore->setTmpFilterData($eiSpecId, $filterData);
		$this->tmpFilterStore->setTmpSortDirections($eiSpecId, $sortDirections);
		
		$mc->addInfoCode('spec_filter_created_info', array('name' => $filter->getName()));
	}
	
	public function deleteFilter() {
		if (null === $this->selectedFilterId || !isset($this->filters[$this->selectedFilterId])) return;

		$this->filterStore->removeFilter($this->filters[$this->selectedFilterId]);
		$eiSpecId = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getId();
		$this->tmpFilterStore->setFilterId($eiSpecId, null);
		$this->tmpFilterStore->setTmpFilterData($eiSpecId, null);
		$this->tmpFilterStore->setTmpSortDirections($eiSpecId, null);
	}
}
