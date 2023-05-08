<?php

namespace rocket\cu\util;

use rocket\cu\util\gui\CufBulkyGui;
use rocket\common\util\RfControlResponse;
use rocket\ei\util\EiuAnalyst;

class CuuFactory {

	function __construct(private readonly CuuAnalyst $cuuAnalyst) {

	}

	function newBulkyGui(bool $readOnly): CufBulkyGui {
		return new CufBulkyGui($readOnly);
	}

	function newControlResponse() {
		return new RfControlResponse($this->cuuAnalyst->getEiu(true)->getEiuAnalyst());
	}
}