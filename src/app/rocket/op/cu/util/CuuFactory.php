<?php

namespace rocket\op\cu\util;

use rocket\op\cu\util\gui\CufBulkyGui;
use rocket\op\util\OpfControlResponse;
use rocket\op\ei\util\EiuAnalyst;

class CuuFactory {

	function __construct(private readonly CuuAnalyst $cuuAnalyst) {

	}

	function newBulkyGui(bool $readOnly): CufBulkyGui {
		return new CufBulkyGui($readOnly);
	}

	function newControlResponse() {
		return new OpfControlResponse($this->cuuAnalyst->getEiu(true)->getEiuAnalyst());
	}
}