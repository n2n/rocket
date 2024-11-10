<?php

namespace rocket\op\ei\util\gui;

use rocket\op\ei\util\EiuAnalyst;
use rocket\ui\si\control\SiButton;
use rocket\impl\ei\manage\gui\RefGuiControl;
use rocket\ui\gui\Gui;
use rocket\op\ei\manage\gui\factory\EiGuiFactory;

class EiuGuiFactory {

	public function __construct(private EiuAnalyst $eiuAnalyst) {
	}

	public function createBulkyGui(array $eiEntries, bool $readOnly): Gui {
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createBulkyGui($eiEntries, $readOnly);
	}

	function createCompactExploreGui(int $pageSize = 30, bool $populateFirstPage = true): \rocket\impl\ei\manage\gui\CompactExplorerGui {
		$factory = new EiGuiFactory($this->eiuAnalyst->getEiFrame(true));
		return $factory->createCompactExplorerGui($pageSize, $populateFirstPage);
	}
}