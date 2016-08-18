<?php
namespace rocket\script\entity\filter\item;

use n2n\dispatch\option\impl\EnumOption;
use n2n\util\Attributes;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\filter\SelectorConstraint;
use n2n\core\IllegalStateException;
use n2n\core\Message;
use rocket\script\entity\SelectorValidationResult;

class EnumFilterItem extends SimpleFilterItem {
	
	public function __construct($propertyName, $label, array $operatorOptions, array $options) {
		parent::__construct($propertyName, $label, $operatorOptions, new EnumOption('Value', $options));
	}
}