<?php

namespace rocket\attribute\impl;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EiPropOneToOneEmbedded {

	function __construct(public bool $reduced = true) {
	}
}