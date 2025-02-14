<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\si\content\impl\relation\SiEmbeddedEntryFactory;
use rocket\ui\si\content\impl\relation\SiEmbeddedEntry;

class GuiEmbeddedEntriesCollection implements SiEmbeddedEntryFactory {

	private \WeakMap $siGuiMap;

	function __construct(private GuiEmbeddedEntryFactory $model) {
		$this->siGuiMap = new \WeakMap();
	}

	function add(GuiEmbeddedEntry $embeddedEntry): SiEmbeddedEntry {
		$siEmbeddedEntry = $embeddedEntry->getSiEmbeddedEntry();
		$this->siGuiMap->offsetSet($siEmbeddedEntry, $embeddedEntry);
		return $siEmbeddedEntry;
	}

	function siToGui(SiEmbeddedEntry $siEmbeddedEntry): ?GuiEmbeddedEntry {
		return $this->siGuiMap->offsetGet($siEmbeddedEntry);
	}

	function createSiEmbeddedEntry(string $maskId): SiEmbeddedEntry {
		$guiEmbeddedEntry = $this->model->createNewGuiEmbeddedEntry($maskId);
		$siEmbeddedEntry = $guiEmbeddedEntry->getSiEmbeddedEntry();
		$this->siGuiMap->offsetSet($siEmbeddedEntry, $guiEmbeddedEntry);
		return $siEmbeddedEntry;
	}
}