<?php

namespace rocket\attribute\impl;
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EiEditConfig {

	function __construct(public ?bool $mandatory = null, public ?bool $readOnly = null, public ?bool $constant = null) {

	}
}