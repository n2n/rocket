<?php

namespace rocket\script\entity\filter;

use n2n\reflection\ArgumentUtils;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\persistence\orm\criteria\CriteriaProperty;

class ComparatorConstraintGroup implements ComparatorConstraint {
	private $useAndEnabled;
	private $comparatorConstraints;
	
	public function __construct($useAnd, array $comparatorConstraints = array()) {
		$this->setUseAndEnabled($useAnd);
		$this->setComparatorConstraints($comparatorConstraints);
	}
	
	public function isUseAndEnabled() {
		return $this->useAndEnabled;
	}
	
	public function setUseAndEnabled($useAndEnabled) {
		$this->useAndEnabled = (boolean) $useAndEnabled;
	}
		
	public function getComparatorConstraints() {
		return $this->comparatorConstraints;
	}

	public function setComparatorConstraints(array $comparatorConstraints) {
		ArgumentUtils::validateArrayType($comparatorConstraints, 'rocket\script\entity\filter\ComparatorConstraint');
		$this->comparatorConstraints = $comparatorConstraints;
	}
	
	public function addComparatorConstraint(ComparatorConstraint $comparatorConstraint) {
		$this->comparatorConstraints[] = $comparatorConstraint;
	}
	
	public function isEmpty() {
		return empty($this->comparatorConstraints);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\ComparatorConstraint::applyToCriteriaComparator()
	 */
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias, $useAnd) {
		if ($this->isEmpty()) return;
		
		$groupComparator = $criteriaComparator->group($useAnd);
		foreach ($this->comparatorConstraints as $comparatorConstraint) {
			$comparatorConstraint->applyToCriteriaComparator($groupComparator, $alias, $this->useAndEnabled);
		}
	}

}