<?php

namespace rocket\cu\gui\impl;

class CuiGuis {

	static function bulky(bool $readOnly): BulkyCuGui {
		return new BulkyCuGui();
	}
}