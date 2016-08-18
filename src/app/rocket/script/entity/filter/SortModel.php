<?php

namespace rocket\script\entity\filter;

use n2n\reflection\ArgumentUtils;
use rocket\script\entity\filter\item\SortItem;

class SortModel {
	private $sortItems = array();
	
	public function putSortItem($id, SortItem $sortItem) {
		$this->sortItems[$id] = $sortItem;	
	}
	
	public function getSortItems() {
		return $this->sortItems;
	}
	
	public function setSortItems(array $sortItems) {
		$this->sortItems = $sortItems;
	}
	
	public function createCriteriaConstraint(array $sortDirections) {
		$fcc = new FilterCriteriaConstraint();
		
		foreach ($sortDirections as $id => $direction) {
			if (isset($this->sortItems[$id])) {
				$criteriaConstraint = $this->sortItems[$id]->createSortCriteriaConstraint($direction);
				ArgumentUtils::validateReturnType($criteriaConstraint, 'rocket\script\entity\filter\SortCriteriaConstraint',
						$this->sortItems[$id], 'createSortCriteriaConstraint');
				$fcc->addCriteriaConstraint($criteriaConstraint);
			}	
		}
		
		if ($fcc->isEmpty()) {
			return null;
		}
		
		return $fcc;
	}
}