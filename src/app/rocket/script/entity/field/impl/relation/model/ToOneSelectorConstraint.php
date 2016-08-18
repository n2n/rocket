<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\filter\SelectorConstraint;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\core\IllegalStateException;
use n2n\core\Message;
use rocket\script\entity\EntityScript;
class ToOneSelectorConstraint implements SelectorConstraint {
	private $targetEntityScript;
	
	private $operator;
	private $comparableValue;
	
	public function __construct($operator, $comparableValue, EntityScript $targetEntityScript) {
		$this->operator = $operator;
		$this->comparableValue = $comparableValue;
		$this->targetEntityScript = $targetEntityScript; 
	}
	
	private function prepareValue($value) {
		if ($value === null) return $value;
		return (string) $value;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\SelectorConstraint::matches()
	 */
	public function matches($value) {
		if ($value === null) return $this->comparableValue === null;
		
		$currentId = $this->prepareValue($this->targetEntityScript->extractId($value));
		if ($this->operator === CriteriaComparator::OPERATOR_EQUAL) {
			return $this->comparableValue === $currentId;
		}
		if ($this->operator === CriteriaComparator::OPERATOR_NOT_EQUAL) {
			return $this->comparableValue !== $currentId;
		}
		throw new IllegalStateException('Unsupported operator ' . $this->operator);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\SelectorConstraint::validate()
	 */
	public function validate($value) {
		if ($this->matches($value)) return null;
		return new Message('does not match');
	}
}
