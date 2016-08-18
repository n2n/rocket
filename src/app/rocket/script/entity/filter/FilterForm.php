<?php

namespace rocket\script\entity\filter;

use rocket\script\entity\filter\item\FilterItem;
use n2n\dispatch\option\impl\OptionForm;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use n2n\util\Attributes;
use n2n\dispatch\Dispatchable;
use rocket\script\entity\filter\data\FilterData;
use rocket\script\entity\filter\data\FilterDataUsage;
use rocket\script\entity\filter\data\FilterDataGroup;
use rocket\script\entity\filter\data\FilterDataElement;

class FilterForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('groupAndUsed', 'groupParentKeys')));
		$as->p('filterItemForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
	}
	
	private $filterItemForms = array();
	private $groupAndUsed = array();
	private $groupParentKeys = array();
	
	public static function createFromFilterModel(FilterModel $filterModel) {
		return self::createFromFilterItems($filterModel->getFilterItems());
	}
		
	public static function createFromFilterItems(array $filterItems) {
		$filterForm = new FilterForm();
		foreach ($filterItems as $id => $filterItem) {
			$filterForm->putFilterItemForm($id, new FilterItemForm($filterItem));
		}
		return $filterForm;
	}
	
	public function getFilterItemForms() {
		return $this->filterItemForms;
	}
	
	public function setFilterItemForms(array $filterItemForms) {
		$this->filterItemForms = $filterItemForms;
	}
	
	public function putFilterItemForm($id, FilterItemForm $filterItemModel) {
		$this->filterItemForms[$id] = $filterItemModel;
	}
	
	public function setGroupAndUsed(array $groupAndUsed) {
		$this->groupAndUsed = $groupAndUsed;
	}
	
	public function getGroupAndUsed() {
		return $this->groupAndUsed;
	}
	
	public function getGroupParentKeys() {
		return $this->groupParentKeys;
	}
	
	public function setGroupParentKeys(array $groupParentKeys) {
		$this->groupParentKeys = $groupParentKeys;
	}
	
	private function _validation() { }
	
	public function writeFilterData(FilterData $filterData) {
		foreach ($filterData->getElements() as $element) {
			$this->writeFilterDataElement($element);
		}
		return $filterData;
	}
	
	private function writeFilterDataElement(FilterDataElement $element, $groupKey = null) {
		if ($element instanceof FilterDataUsage) {
			$itemId = $element->getItemId();
			if (isset($this->filterItemForms[$itemId])) {
				$this->filterItemForms[$itemId]->createUsage($element->getAttributes())
						->setGroupKey($groupKey);
			}
			
			return;
		} 
		
		if ($element instanceof FilterDataGroup) {
			$currentGroupKey = sizeof($this->groupAndUsed);
			$this->groupAndUsed[$currentGroupKey] = $element->isAndUsed();
			$this->groupParentKeys[$currentGroupKey] = $groupKey;
			foreach ($element->getAll() as $childElement) {
				$this->writeFilterDataElement($childElement, $currentGroupKey);
			}
		}
	}
	
	public function readFilterData() {
		$filterData = new FilterData();
		$filterDataWriter = new FilterDataBuilder($this->filterItemForms, $this->groupAndUsed, $this->groupParentKeys);
		$filterDataWriter->write($filterData);
		return $filterData;
	}
}


class FilterDataBuilder {
	private $filterItemModels;
	private $groupAndUsed;
	private $groupParentKeys;
	
	private $assignedGroups = array();
	
	public function __construct(array $filterItemModels, array $groupAndUsed, array $groupParentKeys) {
		$this->filterItemModels = $filterItemModels;
		$this->groupAndUsed = $groupAndUsed;
		$this->groupParentKeys = $groupParentKeys;
	}
	
	private function findGroup($key, FilterData $filterData = null) {
		if (isset($this->assignedGroups[$key])) {
			return $this->assignedGroups[$key];
		}
		
		$group = new FilterDataGroup();
		$group->setAndUsed(isset($this->groupAndUsed[$key]) && $this->groupAndUsed[$key]);
		
		if (isset($this->groupParentKeys[$key])) {
			$this->findGroup($this->groupParentKeys[$key])->add($group);
		} else if ($filterData !== null) {
			$filterData->addElement($group);
		}
		
		return $this->assignedGroups[$key] = $group;
	}
	
	public function write(FilterData $filterData) {
		$filterData->clear();
		
		foreach ($this->filterItemModels as $id => $filterItemModel) {
			foreach ($filterItemModel->getUsages() as $usage) {				
				$fdu = new FilterDataUsage($id, $usage->getOptionForm()->getAttributes());
				
				if (null !== ($groupKey = $usage->getGroupKey())) {
					$this->findGroup($groupKey, $filterData)->add($fdu);
					continue;
				}
				
				$filterData->addElement($fdu);
			}
		}
	}
}


class FilterItemForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array()));
		$as->p('usages', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY, 
				array('creator' => function (FilterItemForm $filterItemForm) {
					return new FilterItemUsageForm(new OptionForm(
							$filterItemForm->filterItem->createOptionCollection(), new Attributes()));
				}));
	}
	
	protected $filterItem;
	private $usages = array();
	
	public function __construct(FilterItem $filterItem) {
		$this->filterItem = $filterItem;
	}
	
	public function getLabel() {
		return $this->filterItem->getLabel();
	}
	
	public function getUsages() {
		return $this->usages;
	}
	
	public function setUsages(array $usages) {
		$this->usages = $usages;
	}
	
	public function createUsage(Attributes $attributes) {
		return $this->usages[] = new FilterItemUsageForm(new OptionForm(
				$this->filterItem->createOptionCollection(), $attributes));
	}
	
	private function _validation() { }
}

class FilterItemUsageForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, 
				array('names' => array('optionForm', 'groupKey')));
		$as->p('optionForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
	}
	
	private $optionForm;
	private $groupKey;
	
	public function __construct(OptionForm $optionForm) {
		$this->optionForm = $optionForm;
	}
	
	public function getOptionForm() {
		return $this->optionForm;
	}
	
	public function setOptionForm(OptionForm $optionForm) {
		$this->optionForm = $optionForm;
	}
	
	public function getGroupKey() {
		return $this->groupKey;
	}
	
	public function setGroupKey($groupKey) {
		$this->groupKey = $groupKey;
	}
	
	private function _validation() { }
}