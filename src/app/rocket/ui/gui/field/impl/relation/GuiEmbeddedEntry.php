<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\gui\impl\BulkyGui;
use rocket\ui\gui\impl\CompactGui;
use rocket\ui\si\content\impl\relation\SiEmbeddedEntry;

class GuiEmbeddedEntry {

	function __construct(private BulkyGui $gui, private ?CompactGui $summaryGui = null) {
		$this->siEmbeddedEntry = new SiEmbeddedEntry($this->gui->getSiGui(), $this->summaryGui?->getSiGui());
	}

	function getGui(): BulkyGui {
		return $this->gui;
	}

	function getSiEmbeddedEntry(): SiEmbeddedEntry {
		return $this->siEmbeddedEntry;
	}
}