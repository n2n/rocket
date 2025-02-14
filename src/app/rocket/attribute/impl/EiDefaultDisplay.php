<?php

namespace rocket\attribute\impl;


#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EiDefaultDisplay {

	function __construct(public int $viewModes = 0) {
	}
}