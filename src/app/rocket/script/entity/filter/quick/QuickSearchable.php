<?php

namespace rocket\script\entity\filter\quick;

interface QuickSearchable {
	/**
	 * @param string $searchStr
	 * @return \rocket\script\entity\filter\ComparatorConstraint
	 */
	public function createQuickSearchComparatorConstraint($searchStr);
}