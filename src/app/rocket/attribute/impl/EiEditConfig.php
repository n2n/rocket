<?php

namespace rocket\attribute\impl;

class EiEditConfig {

	function __construct(public ?bool $constant = null, public ?bool $readOnly = null, public ?bool $mandatory = null) {

	}
}