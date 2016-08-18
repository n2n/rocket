<?php
namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\dispatch\option\impl\EnumOption;
use n2n\util\Attributes;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\CriteriaComparator;

class ToManyFilterItem extends SimpleFilterItem {
	/**
	 * @param string $propertyName
	 * @param array $options
	 * @param array $targetEntities
	 */
	public function __construct($propertyName, $label, array $availableOperatorOptions, array $options) {
		parent::__construct($propertyName, $label, $availableOperatorOptions, new EnumOption('Value', $options, null, true));
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\FilterItem::createComparatorConstraints()
	*/
	public function createComparatorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		$id = $attributes->get(self::VALUE_OPTION);
		if ($operator === null || $id === null) return null;
		return new SimpleComparatorConstraint($this->propertyName, $id, CriteriaComparator::OPERATOR_EQUAL);
	}
}