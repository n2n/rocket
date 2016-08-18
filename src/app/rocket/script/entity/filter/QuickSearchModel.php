<?php

namespace rocket\script\entity\filter;

use n2n\reflection\ArgumentUtils;
use rocket\script\entity\filter\quick\QuickSearchable;

class QuickSearchModel {
	private $quickSearchables = array();
	
	public function addQuickSearchable(QuickSearchable $quickSearchable) {
		$this->qickSearchables[] = $quickSearchable;
	}
	
	public function createCriteriaConstraint($searchStr) {
		if (!mb_strlen($searchStr)) return null;
		
		$filterConstraint = new FilterCriteriaConstraint();
		
		$searchStrParts = preg_split('/[\s]+/', $searchStr);
		foreach ($searchStrParts as $key => $searchStrPart) {
			if (!mb_strlen($searchStrPart)) continue;
				
			$group = new ComparatorConstraintGroup(false);
				
			foreach ($this->qickSearchables as $quickSearchable) {
				$comparatorConstraint = $quickSearchable->createQuickSearchComparatorConstraint($searchStrPart);
				ArgumentUtils::validateReturnType($comparatorConstraint, 'rocket\script\entity\filter\ComparatorConstraint', 
						$quickSearchable, 'createQuickSearchComparatorConstraint');
				$group->addComparatorConstraint($comparatorConstraint);
			}
			
			$filterConstraint->addComparatorConstraint($group);
		}
		
		if ($filterConstraint->isEmpty()) return null;
		
		return $filterConstraint;
	}
}