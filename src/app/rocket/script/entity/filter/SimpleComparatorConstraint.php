<?php
namespace rocket\script\entity\filter;

use n2n\persistence\orm\criteria\CriteriaConstant;

use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\CriteriaComparator;

class SimpleComparatorConstraint implements ComparatorConstraint {
	protected $propertyName;
	protected $operator;
	protected $value;
	
	public function __construct($propertyName, $value, $operator = null) {
		$this->propertyName = $propertyName;
		if ($operator === null) {
			$operator = CriteriaComparator::OPERATOR_EQUAL;
		}
		$this->operator = $operator;
		$this->value = $value;
	}
	
	public function applyToCriteriaComparator(CriteriaComparator $criteriaSelector, CriteriaProperty $alias, $useAnd) {
		$criteriaSelector->match($alias->createExtended($this->propertyName), $this->operator,
				new CriteriaConstant($this->value), (boolean) $useAnd);
	}
}