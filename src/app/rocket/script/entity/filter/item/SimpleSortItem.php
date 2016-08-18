<?php
namespace rocket\script\entity\filter\item;

use rocket\script\entity\filter\SortCriteriaConstraint;

class SimpleSortItem implements SortItem {
	protected $propertyName;
	protected $label;
	
	public function __construct($propertyName, $label) {
		$this->propertyName = $propertyName;
		$this->label = $label;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function createSortCriteriaConstraint($direction) {
		return new SortCriteriaConstraint($this->propertyName, $direction);
	}
}