<?php

namespace rocket\impl\cu\gui;

class CuiGuis {

	static function bulky(bool $readOnly): BulkyCuGui {
		return new BulkyCuGui();
	}
}