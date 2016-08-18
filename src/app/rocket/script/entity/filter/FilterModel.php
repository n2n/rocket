<?php

namespace rocket\script\entity\filter;

use rocket\script\entity\filter\item\FilterItem;
use rocket\script\entity\filter\data\FilterData;
use rocket\script\entity\filter\data\FilterDataElement;
use rocket\script\entity\filter\data\FilterDataUsage;
use rocket\script\entity\filter\data\FilterDataGroup;
use n2n\reflection\ArgumentUtils;

class FilterModel {
	private $filterItems = array();
	
	public static function createFromFilterItems(array $filterItems) {
		$filterModel = new FilterModel();
		foreach ($filterItems as $id => $filterItem) {
			$filterModel->putFilterItem($id, $filterItem);
		}
		return $filterModel;
	}
	
	public function putFilterItem($id, FilterItem $filterItem) {
		$this->filterItems[$id] = $filterItem;
	}
	
	public function getFilterItems() {
		return $this->filterItems;
	}
	
	public function setFilterItems(array $filterItems) {
		ArgumentUtils::validateArrayType($filterItems, 'rocket\script\entity\filter\item\FilterItem');
		$this->filterItems = $filterItems;
	}
	
	public function createCriteriaConstraint(FilterData $filterData) {
		$fcc = new FilterCriteriaConstraint();
		foreach ($filterData->getElements() as $element) {
			if (null !== ($comparatorConstraint = $this->createElementComparatorConstraint($element))) {
				$fcc->addComparatorConstraint($comparatorConstraint);
			}
		}
		
		if ($fcc->isEmpty()) return null;
		return $fcc;
	}
	
	public function createComparatorConstraint(FilterData $filterData) {
		$ccg = new ComparatorConstraintGroup(true);
		foreach ($filterData->getElements() as $element) {
			if (null !== ($comparatorConstraint = $this->createElementComparatorConstraint($element))) {
				$ccg->addComparatorConstraint($comparatorConstraint);
			}
		}
		
		if ($ccg->isEmpty()) return null;
		return $ccg;
	}

	private function createElementComparatorConstraint(FilterDataElement $element) {
		if ($element instanceof FilterDataUsage) {
			$itemId = $element->getItemId();
			if (isset($this->filterItems[$itemId])) {
				$comparatorConstraint = $this->filterItems[$itemId]->createComparatorConstraint($element->getAttributes());
				ArgumentUtils::validateReturnType($comparatorConstraint, 
						'rocket\script\entity\filter\ComparatorConstraint',
						$this->filterItems[$itemId], 'createComparatorConstraint');
				return $comparatorConstraint;
			}
		} else if ($element instanceof FilterDataGroup) {
			$group = new ComparatorConstraintGroup($element->isAndUsed());
			foreach ($element->getAll() as $childElement) {
				$group->addComparatorConstraint($this->createElementComparatorConstraint($childElement));
			}
			return $group;
		}
		
		return null;
	}
}