<?php

namespace rocket\op\cu\gui\impl;

class CuiGuis {

	static function bulky(bool $readOnly): BulkyCuGui {
		return new BulkyCuGui();
	}
}