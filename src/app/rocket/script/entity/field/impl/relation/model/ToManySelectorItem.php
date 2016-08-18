<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\filter\item\SelectorItem;
use n2n\util\Attributes;
use rocket\script\entity\EntityScript;
class ToManySelectorItem extends ToManyFilterItem implements SelectorItem {

	private $targetEntitiyScript;
	
	public function __construct($propertyName, $label, $availableOperatorOptions, $options, 
			EntityScript $targetEntityScript) {
		parent::__construct($propertyName, $label, $availableOperatorOptions, $options);
		$this->targetEntitiyScript = $targetEntityScript;
	}
	
	public function createSelectorConstraint(Attributes $attributes) {
		return new ToManySelectorConstraint($attributes->get(self::VALUE_OPTION), $this->targetEntitiyScript);
	}
}