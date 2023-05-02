<?php

namespace rocket\cu\util;

use rocket\cu\util\gui\CufBulkyGui;

class CuuFactory {

	function newBulkyGui(bool $readOnly): CufBulkyGui {
		return new CufBulkyGui($readOnly);
	}
}