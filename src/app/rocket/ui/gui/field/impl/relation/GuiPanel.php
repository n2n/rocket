<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\si\content\impl\relation\SiPanel;
use rocket\ui\si\content\impl\relation\SiEmbeddedEntry;

class GuiPanel {

	private GuiEmbeddedEntriesCollection $collection;
	private SiPanel $siPanel;

	function __construct(string $name, string $label, string $bulkySiMaskId,
			?string $summarySiMaskId, GuiEmbeddedEntryFactory $embeddedEntryFactory) {
		$this->collection = new GuiEmbeddedEntriesCollection($embeddedEntryFactory);
		$this->siPanel = new SiPanel($name, $label, $bulkySiMaskId, $summarySiMaskId, $this->collection);
	}

	function getSiPanel(): SiPanel {
		return $this->siPanel;
	}

	function setGuiEmbeddedEntries(array $guiEmbeddedEntries): static {
		$this->siPanel->setEmbeddedEntries(
				array_map(fn(GuiEmbeddedEntry $e) => $this->collection->add($e), $guiEmbeddedEntries));
		return $this;
	}

	function addGuiEmbeddedEntry(GuiEmbeddedEntry $entry): static {
		$this->siPanel->addEmbeddedEntry($this->collection->add($entry));
		return $this;
	}

	function getGuiEmbeddedEntries(): array {
		return array_map(fn (SiEmbeddedEntry $e) => $this->collection->siToGui($e),
				$this->siPanel->getEmbeddedEntries());
	}

}