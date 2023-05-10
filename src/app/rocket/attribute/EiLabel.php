<?php

namespace rocket\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EiLabel {

	function __construct(public ?string $label = null, public ?string $helpText = null) {
	}

}