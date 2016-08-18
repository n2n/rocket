<?php
namespace rocket\script\entity\command\impl\common\model;

use rocket\script\entity\filter\FilterStore;
use n2n\dispatch\val\ValIsset;
use n2n\dispatch\val\ValEnum;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\Dispatchable;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\map\BindingErrors;
use n2n\core\MessageCode;
use n2n\core\DynamicTextCollection;
use n2n\core\MessageContainer;
use rocket\script\entity\filter\FilterForm;
use rocket\script\entity\filter\SortForm;

class ListFilterForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->p('filterForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('sortForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->m('selectFilter', DispatchAnnotations::MANAGED_METHOD);
		$as->m('apply', DispatchAnnotations::MANAGED_METHOD);
		$as->m('clear', DispatchAnnotations::MANAGED_METHOD);
		$as->m('saveFilter', DispatchAnnotations::MANAGED_METHOD);
		$as->m('createFilter', DispatchAnnotations::MANAGED_METHOD);
		$as->m('deleteFilter', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $dtc;
	private $scriptState;
	
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
	
	public function __construct(ScriptState $scriptState, FilterStore $filterStore, ListTmpFilterStore $tmpFilterStore) {
		$this->dtc = new DynamicTextCollection('rocket', $scriptState->getLocale());
		$this->scriptState = $scriptState;
		$this->filterStore = $filterStore;
		$this->tmpFilterStore = $tmpFilterStore;

		$this->filterModel = $scriptState->getOrCreateFilterModel();
		$this->filterForm = FilterForm::createFromFilterModel($this->filterModel);
		$this->sortModel = $scriptState->getOrCreateSortModel();
		$this->sortForm = SortForm::createFromSortModel($this->sortModel);
		
		$contextEntityScriptId = $scriptState->getContextEntityScript()->getId();
		
		foreach ($this->filterStore->getFiltersByEntityScriptId($contextEntityScriptId) as $filter) {
			$this->filters[$filter->getId()] = $filter;
		}
		
		$this->selectedFilterId = $tmpFilterStore->getFilterId($contextEntityScriptId);
		
		if (null !== ($tmpFilterData = $tmpFilterStore->getTmpFilterData($contextEntityScriptId))) {
			$this->filterForm->writeFilterData($tmpFilterData);
		}
		
		if (null !== ($sortDirections = $tmpFilterStore->getTmpSortDirections($contextEntityScriptId))) {
			$this->sortForm->setSortDirections($sortDirections);
		}
	}
	
	public function applyToScriptState(ScriptState $scriptState) {
		$filterCriteriaConstraint = $this->filterModel->createCriteriaConstraint(
				$this->filterForm->readFilterData());
		if ($filterCriteriaConstraint !== null) {
			$scriptState->addCriteriaConstraint($filterCriteriaConstraint);
		}
		
		$sortDirections = $this->sortForm->getSortDirections();
		if (empty($sortDirections)) {
			$sortDirections = $this->scriptState->getScriptMask()->getDefaultSortDirections();
		}
		
		$sortCriteriaConstraint = $this->sortModel->createCriteriaConstraint($sortDirections);
		if ($sortCriteriaConstraint !== null) {
			$scriptState->addCriteriaConstraint($sortCriteriaConstraint);
		}
	}	
	
	public function getScriptId() {
		return $this->scriptState->getContextEntityScript()->getId();
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
			$filterIdOptions[null] = $this->dtc->translate('script_cmd_list_unsaved_filter_label');
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
	
	public function getSortableScriptFieldIds() {
		return array_keys($this->sortableScriptFields);
	}
	
	private function _validation(BindingConstraints $bc) {
		if ('createFilter' == $bc->getMethodName()) {
			$bc->val('newFilterName', new ValIsset());
			
			$filterStore = $this->filterStore;
			$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
			$bc->addClosureValidator(function ($newFilterName, BindingErrors $be) use ($filterStore, $entityScriptId, $bc) {
				if ($filterStore->containsFilterName($entityScriptId, $newFilterName)) {
					$be->addError('newFilterName', new MessageCode('script_cmd_list_filter_name_already_exists'));
				}
			});
			
		} else {
			$bc->val('selectedFilterId', new ValEnum(array_keys($this->getSelectedFilterIdOptions()), false));
		}
	}
	
	public function selectFilter() {
		$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
		
		$filterData = null;
		$sortDirections = null;
		if (isset($this->filters[$this->selectedFilterId])) {
			$filter = $this->filters[$this->selectedFilterId];
			$filterData = $filter->readFilterData();
			$sortDirections = $filter->getSortDirections();
		}
		
		$this->tmpFilterStore->setFilterId($entityScriptId, $this->selectedFilterId);
		$this->tmpFilterStore->setTmpFilterData($entityScriptId, $filterData);
		$this->tmpFilterStore->setTmpSortDirections($entityScriptId, $sortDirections);
	}
	
	public function apply() {
		$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
		$this->tmpFilterStore->setFilterId($entityScriptId, null);
		$this->tmpFilterStore->setTmpFilterData($entityScriptId, 
				$this->filterForm->readFilterData());
		$this->tmpFilterStore->setTmpSortDirections($entityScriptId, 
				$this->sortForm->getSortDirections());
	}
	
	public function clear() {
		$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
		$this->tmpFilterStore->setFilterId($entityScriptId, null);
		$this->tmpFilterStore->setTmpFilterData($entityScriptId, null);
		$this->tmpFilterStore->setTmpSortDirections($entityScriptId, null);
	}
	
	public function saveFilter(MessageContainer $mc) {
		if (null === $this->selectedFilterId || !isset($this->filters[$this->selectedFilterId])) return;
		
		$filter = $this->filters[$this->selectedFilterId];
		
		$filterData = $this->filterForm->readFilterData();
		$filter->writeFilterData($filterData);
		
		$sortDirections = $this->sortForm->getSortDirections();
		$filter->setSortDirections($sortDirections);
	
		$filter = $this->filterStore->mergeFilter($filter);
		
		$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
		$this->tmpFilterStore->setFilterId($entityScriptId, $this->selectedFilterId);
		$this->tmpFilterStore->setTmpFilterData($entityScriptId, $filterData);
		$this->tmpFilterStore->setTmpSortDirections($entityScriptId, $sortDirections);
		
		$mc->addInfoCode('script_filter_saved_info', array('name' => $filter->getName()));
	}
	
	public function createFilter(MessageContainer $mc) {
		$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
				
		$filterData = $this->filterForm->readFilterData();
		$sortDirections = $this->sortForm->getSortDirections();
		
		$filter = $this->filterStore->createFilter($entityScriptId, $this->newFilterName, 
				$filterData, $sortDirections);

		$this->tmpFilterStore->setFilterId($entityScriptId, $filter->getId());
		$this->tmpFilterStore->setTmpFilterData($entityScriptId, $filterData);
		$this->tmpFilterStore->setTmpSortDirections($entityScriptId, $sortDirections);
		
		$mc->addInfoCode('script_filter_created_info', array('name' => $filter->getName()));
	}
	
	public function deleteFilter() {
		if (null === $this->selectedFilterId || !isset($this->filters[$this->selectedFilterId])) return;

		$this->filterStore->removeFilter($this->filters[$this->selectedFilterId]);
		$entityScriptId = $this->scriptState->getContextEntityScript()->getId();
		$this->tmpFilterStore->setFilterId($entityScriptId, null);
		$this->tmpFilterStore->setTmpFilterData($entityScriptId, null);
		$this->tmpFilterStore->setTmpSortDirections($entityScriptId, null);
	}
}