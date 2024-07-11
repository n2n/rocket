<?php

namespace rocket\op\ei\manage\gui;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\ui\gui\GuiValueBoundary;
use rocket\op\ei\component\EiGuiMaskFactory;
use rocket\op\ei\manage\frame\EiFrame;

class EiGuiValueBoundaryFactory {

	function __construct(private readonly EiFrame $eiFrame) {

	}

	/**
	 * @param int|null $treeLevel
	 * @param EiEntry[] $eiEntries
	 * @return GuiValueBoundary
	 */
	function create(?int $treeLevel, array $eiEntries, int $viewMode): GuiValueBoundary {
		$guiValueBoundary = new GuiValueBoundary($treeLevel);

		$eiGuiEntryFactory = new EiGuiEntryFactory($this->eiFrame);
		foreach ($eiEntries as $eiEntry) {
			$guiEntry = $eiGuiEntryFactory->createGuiEntry($eiEntry, $viewMode, true);
			$guiValueBoundary->putGuiEntry($guiEntry);
		}

		if (count($eiEntries) === 1 && isset($guiEntry)) {
			$guiValueBoundary->selectGuiEntryByMaskId($guiEntry->getSiEntryQualifier()->getIdentifier()->getMaskIdentifier()->getId());
		}

		return $guiValueBoundary;
	}
}