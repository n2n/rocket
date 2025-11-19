<?php

namespace rocket\attribute\impl;


#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EiDisplayConfig {

	function __construct(public int $defaultViewModes = 0) {
	}
}