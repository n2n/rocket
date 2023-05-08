<?php

namespace rocket\cu\util;

use rocket\cu\util\gui\CufBulkyGui;
use rocket\common\util\RfControlResponse;
use rocket\ei\util\EiuAnalyst;

class CuuFactory {

	function __construct(private readonly EiuAnalyst $eiuAnalyst) {

	}

	function newBulkyGui(bool $readOnly): CufBulkyGui {
		return new CufBulkyGui($readOnly);
	}

	function newControlResponse() {
		return new RfControlResponse($this->eiuAnalyst);
	}
}