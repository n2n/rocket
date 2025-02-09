<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\gui\impl\BulkyGui;
use rocket\ui\gui\impl\CompactGui;
use rocket\ui\si\content\impl\relation\SiEmbeddedEntry;

class GuiEmbeddedEntry {
	private SiEmbeddedEntry $siEmbeddedEntry;

	function __construct(private BulkyGui $gui, private ?CompactGui $summaryGui = null) {
		$this->siEmbeddedEntry = new SiEmbeddedEntry($this->gui->getSiGui(), $this->summaryGui?->getSiGui());
	}

	function getGui(): BulkyGui {
		return $this->gui;
	}

	function setSummaryGui(CompactGui $summaryGui): void {
		$this->summaryGui = $summaryGui;
		$this->siEmbeddedEntry->setSummaryContent($summaryGui->getSiGui());
	}

	function getSiEmbeddedEntry(): SiEmbeddedEntry {
		return $this->siEmbeddedEntry;
	}
}