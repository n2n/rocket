<?php

namespace rocket\ui\gui\field\impl\relation;

interface GuiEmbeddedEntryFactory {

	function createNewGuiEmbeddedEntry(string $maskId): GuiEmbeddedEntry;
}