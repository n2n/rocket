<?php

namespace rocket\script\entity\filter\item;

interface SortItem {
	public function getLabel();
	/**
	 * @param string $direction
	 * @return SortCriteriaConstraint
	 */
	public function createSortCriteriaConstraint($direction);
}