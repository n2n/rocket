<?php
namespace rocket\script\entity\filter\item;

use n2n\dispatch\option\impl\StringOption;

class TextFilterItem extends SimpleFilterItem {
	
	public function __construct($propertyName, $label, array $operatorOptions) {
		parent::__construct($propertyName, $label, $operatorOptions, new StringOption('Value'));
	}
	
}