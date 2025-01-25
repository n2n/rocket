<?php

namespace rocket\op\ei\util\gui;

use rocket\op\ei\util\EiuAnalyst;
use rocket\ui\gui\Gui;
use rocket\op\ei\manage\gui\factory\EiGuiFactory;
use rocket\ui\gui\impl\BulkyGui;
use rocket\ui\gui\impl\CompactGui;

class EiuGuiFactory {

	public function __construct(private EiuAnalyst $eiuAnalyst) {
	}

	public function createBulkyGui(array $eiEntryArgs, bool $readOnly): BulkyGui {
		$eiEntries = EiuAnalyst::buildEiEntriesFromEiArg($eiEntryArgs);
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createBulkyGui($eiEntries, $readOnly);
	}

	public function createCompactGui(array $eiEntryArgs, bool $readOnly): CompactGui {
		$eiEntries = EiuAnalyst::buildEiEntriesFromEiArg($eiEntryArgs);
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createCompactGui($eiEntries, $readOnly);
	}

	function createCompactExploreGui(int $pageSize = 30, bool $populateFirstPage = true): \rocket\ui\gui\impl\CompactExplorerGui {
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createCompactExplorerGui($pageSize, $populateFirstPage);
	}
}