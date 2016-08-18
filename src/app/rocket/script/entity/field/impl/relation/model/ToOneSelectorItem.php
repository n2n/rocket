<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\filter\item\SelectorItem;
use n2n\util\Attributes;
use rocket\script\entity\EntityScript;
class ToOneSelectorItem extends ToOneFilterItem implements SelectorItem {

	private $targetEntitiyScript;
	
	public function __construct($propertyName, $label, $availableOperatorOptions, $options, $targetEntities, 
			EntityScript $targetEntityScript) {
		parent::__construct($propertyName, $label, $availableOperatorOptions, $options, $targetEntities);
		$this->targetEntitiyScript = $targetEntityScript;
	}
	
	public function createSelectorConstraint(Attributes $attributes) {
		return new ToOneSelectorConstraint($attributes->get(self::OPERATOR_OPTION), 
				$attributes->get(self::VALUE_OPTION), $this->targetEntitiyScript);
	}
}