<?php
namespace rocket\script\entity\filter;

use rocket\script\entity\manage\CriteriaConstraint;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\CriteriaProperty;

class SortCriteriaConstraint implements CriteriaConstraint {
	private $propertyName;
	private $direction;
	
	public function __construct($propertyName, $direction) {
		$this->propertyName = $propertyName;
		$this->direction = $direction;
	}
	
	public function getPropertyName() {
		return $this->propertyName;
	}
	
	public function getDirection() {
		return $this->direction;
	}
	
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {
		$criteria->order($alias->createExtended($this->propertyName), 
				$this->direction);
	}
}