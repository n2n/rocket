<?php
namespace rocket\op\ei\manage\gui;

use PHPUnit\Framework\TestCase;
use rocket\ui\gui\ViewMode;

class ViewModeTest extends TestCase {
	
	function testIsBulky() {
		$this->assertTrue(ViewMode::isBulky(ViewMode::BULKY_ADD));
		$this->assertTrue(ViewMode::isBulky(ViewMode::BULKY_EDIT));
		$this->assertTrue(ViewMode::isBulky(ViewMode::BULKY_READ));
		$this->assertFalse(ViewMode::isBulky(ViewMode::COMPACT_ADD));
		$this->assertFalse(ViewMode::isBulky(ViewMode::COMPACT_EDIT));
		$this->assertFalse(ViewMode::isBulky(ViewMode::COMPACT_READ));
	}
	
}