<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\si\content\impl\relation\SiPanel;

class GuiPanel {

	function __construct(string $name, string $label, string $bulkySiMaskId,
			?string $summarySiMaskId, GuiEmbeddedEntryFactory $embeddedEntryFactory) {
		$this->collection = new GuiEmbeddedEntriesCollection($embeddedEntryFactory);
		$this->siPanel = new SiPanel($name, $label, $bulkySiMaskId, $summarySiMaskId, $this->collection);
	}

	function getSiPanel(): SiPanel {
		return $this->siPanel;
	}


}