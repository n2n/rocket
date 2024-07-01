<?php

namespace rocket\op\ei\manage\gui;

use rocket\op\ei\manage\entry\EiEntry;

class EiGuiValueBoundaryFactory {

	function __construct() {

	}

	/**
	 * @param int|null $treeLevel
	 * @param EiEntry[] $eiEntries
	 * @return void
	 */
	function create(?int $treeLevel, array $eiEntries, int $viewMode) {

		foreach ($eiEntries as $eiEntry) {
			$eiEntry->getEiMask()->getEiEngine()->getEiGuiDefinition($viewMode)->createGuiEntry($eiEntry, $this->eiFrame);
		}
	}
}