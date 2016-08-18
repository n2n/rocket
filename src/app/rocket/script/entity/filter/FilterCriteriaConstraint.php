<?php
namespace rocket\script\entity\filter;

use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;
use rocket\script\entity\manage\CriteriaConstraint;
use n2n\reflection\ArgumentUtils;

class FilterCriteriaConstraint implements CriteriaConstraint {
	private $comparatorConstraints = array();
	private $criteriaConstraints = array();
	
	public function __construct(ComparatorConstraint $selectorConstraint = null) {
		if ($selectorConstraint !== null) {
			$this->addComparatorConstraint($selectorConstraint);
		}
	}
	
	public function isEmpty() {
		return empty($this->comparatorConstraints) && empty($this->criteriaConstraints);
	}
	
	public function getComparatorConstraints() {
		return $this->comparatorConstraints;
	}
	
	public function setComparatorConstraints(array $comparatorConstraints) {
		ArgumentUtils::validateArrayType($comparatorConstraints, 'rocket\script\entity\filter\ComparatorConstraint');
		$this->comparatorConstraints = $comparatorConstraints;
	}
	
	public function addComparatorConstraint(ComparatorConstraint $selectorConstraint) {
		$this->comparatorConstraints[] = $selectorConstraint;
	}
	
	public function getCriteriaConstraints() {
		return $this->criteriaConstraints;
	}
	
	public function setCriteriaConstraints(array $criteriaConstraints) {
		ArgumentUtils::validateArrayType($criteriaConstraints, 'rocket\script\entity\filter\CriteriaConstraint');
		$this->criteriaConstraints = $criteriaConstraints;
	}
	
	public function addCriteriaConstraint(CriteriaConstraint $criteriaConstraint) {
		$this->criteriaConstraints[] = $criteriaConstraint;
	}
	
	public function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {
		$groupComparator = $criteria->where()->andGroup();
		foreach ($this->comparatorConstraints as $comparatorConstraint) {
			$comparatorConstraint->applyToCriteriaComparator($groupComparator, $alias, true);
		}
		
		foreach ($this->criteriaConstraints as $criteriaConstraint) {
			$criteriaConstraint->applyToCriteria($criteria, $alias);
		}
	}
}