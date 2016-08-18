<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\filter\SelectorConstraint;
use n2n\core\Message;
use rocket\script\entity\EntityScript;
use n2n\reflection\ArgumentUtils;

class ToManySelectorConstraint implements SelectorConstraint {

	private $targetEntityScript;
	
	private $comparableValue;
	
	public function __construct($comparableValue, EntityScript $targetEntityScript) {
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
		if ($value === null) return false;
		
		ArgumentUtils::assertTrue($value instanceof \ArrayObject);
		
		foreach ($value as $entity) {
			$currentId = $this->prepareValue($this->targetEntityScript->extractId($entity));
			if ($currentId === $this->comparableValue) return true; 
		}
		return false;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\SelectorConstraint::validate()
	 */
	public function validate($value) {
		if ($this->matches($value)) return null;
		return new Message('does not match');
	}
}
