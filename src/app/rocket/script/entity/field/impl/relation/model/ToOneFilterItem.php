<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\util\Attributes;
use n2n\dispatch\option\impl\EnumOption;
use rocket\script\entity\filter\SimpleComparatorConstraint;

class ToOneFilterItem extends SimpleFilterItem {
	private $targetEntities;
	/**
	 * @param string $propertyName
	 * @param array $options
	 * @param array $targetEntities
	 */
	public function __construct($propertyName, $label, array $availableOperatorOptions, array $options, array $targetEntities) {
		parent::__construct($propertyName, $label, $availableOperatorOptions, new EnumOption('Value', $options));
		$this->targetEntities = $targetEntities;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\FilterItem::createComparatorConstraints()
	 */
	public function createComparatorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		if ($operator === null) return null;
		
		$targetEntity = null;
		$key = $attributes->get(self::VALUE_OPTION);
		if (isset($this->targetEntities[$key]))  {
			$targetEntity = $this->targetEntities[$key];
		}
		
		return new SimpleComparatorConstraint($this->propertyName, $targetEntity, $operator);
	}	
}