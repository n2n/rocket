<?php

namespace rocket\attribute\impl;

use Attribute;
use n2n\util\type\ArgUtils;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Addon {

	/**
	 * e. g. '{icon:fas fa-beer}','CHF', 'text with {icon:fas fa-beer}'
	 *
	 * @param string[] $prefixes
	 * @param string[] $suffixes
	 */
	function __construct(public readonly array $prefixes, public readonly  array $suffixes) {
		ArgUtils::valArray($this->prefixes, 'string');
		ArgUtils::valArray($this->suffixes, 'string');
	}
}