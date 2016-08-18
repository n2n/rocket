<?php

namespace rocket\script\entity\manage;

use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\Entity;
use rocket\script\entity\filter\SimpleComparatorConstraint;

class SimpleSelectorConstraint extends SimpleComparatorConstraint implements SelectorConstraint {
	private $entityProperty;
	private $matcher;
	
	public function __construct(EntityProperty $entityProperty, $value, \Closure $matcher, $operator = null) {
		parent::__construct($entityProperty->getName(), $value, $operator);

		$this->entityProperty = $entityProperty;
		$this->matcher = $matcher;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\SelectorConstraint::match()
	 */
	public function matches(Entity $entity) {
		return (boolean)$this->matcher->__invoke($this->value,
				$this->entityProperty->getAccessProxy()->getValue($entity));
	}

	
	
}
