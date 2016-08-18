<?php
namespace rocket\script\entity\field\impl\l10n;

use rocket\script\entity\filter\item\EnumFilterItem;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use n2n\l10n\Locale;
use n2n\util\Attributes;

class LocaleFilterItem extends EnumFilterItem {
	/**
	 * @param string $propertyName
	 * @param array $options
	 * @param array $targetEntities
	 */
	public function __construct($propertyName, $label, array $availableOperatorOptions, array $options) {
		parent::__construct($propertyName, $label, $availableOperatorOptions, $options);
	}	
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\item\FilterItem::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		if ($operator === null) return null;
	
		return new SimpleComparatorConstraint($this->propertyName,
				Locale::create($attributes->get(self::VALUE_OPTION)), $operator);
	}
}
