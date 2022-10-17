<?php

namespace rocket\attribute\impl;

use rocket\ei\EiPropPath;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EiPropOneToManyEmbedded {

	public ?EiPropPath $targetOrderEiPropPath;

	function __construct(public bool $reduced = true, ?string $targetOrderProp = null) {
		$this->targetOrderEiPropPath = EiPropPath::build($targetOrderProp);
	}
}