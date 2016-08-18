<?php

namespace rocket\script\entity\filter\item;

use n2n\core\IllegalStateException;
use n2n\core\Message;
use rocket\script\entity\filter\SelectorConstraint;
use n2n\util\Attributes;
use n2n\persistence\orm\criteria\CriteriaComparator;

class EnumSelectorItem extends EnumFilterItem implements SelectorItem {
	
	public function __construct($propertyName, $label, array $operatorOptions, array $options) {
		parent::__construct($propertyName, $label, $operatorOptions, $options);
	}
	
	public function createSelectorConstraint(Attributes $attributes) {
		return new StringSelectorConstraint($attributes->get(self::OPERATOR_OPTION), 
				$attributes->get(self::VALUE_OPTION));
	}
}

class StringSelectorConstraint implements SelectorConstraint {
	private $operator;
	private $comparableValue;
	
	public function __construct($operator, $comparableValue) {
		$this->operator = $operator;
		$this->comparableValue = $comparableValue;
	}
	
	private function prepareValue($value) {
		if ($value === null) return $value;
		return (string) $value;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\SelectorConstraint::matches()
	 */
	public function matches($value) {
		$value = $this->prepareValue($value);
		switch ($this->operator) {
			case CriteriaComparator::OPERATOR_EQUAL:
				return $value === $this->comparableValue;
			case CriteriaComparator::OPERATOR_NOT_EQUAL:
				return $value !== $this->comparableValue;
			case CriteriaComparator::OPERATOR_LARGER_THAN:
				return $value > $this->comparableValue;
			case CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO:
				return $value >= $this->comparableValue;
			case CriteriaComparator::OPERATOR_SMALLER_THAN:
				return $value < $this->comparableValue;
			case CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO:
				return $value <=  $this->comparableValue;
			default:
				throw new IllegalStateException('Unsupported operator ' . $this->operator);
		}
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\SelectorConstraint::validate()
	 */
	public function validate($value) {
		if ($this->matches($value)) return null;
		
		return new Message('does not match');
	}
}