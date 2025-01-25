<?php

namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\op\ei\util\frame\EiuFrame;
use rocket\ui\gui\field\impl\relation\GuiEmbeddedEntryFactory;
use rocket\ui\gui\field\impl\relation\GuiEmbeddedEntry;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\util\entry\EiuEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;

class RelationGuiEmbeddedEntryFactory implements GuiEmbeddedEntryFactory {

	private Eiu $eiu;

	private \WeakMap $eiuEntriesMap;

	function __construct(private EiuFrame $eiuFrame, private bool $summaryRequired) {
		$this->eiu = new Eiu($this->eiuFrame);
	}

	/**
	 * @param EiuEntry[] $eiuEntries
	 * @return GuiEmbeddedEntry[]
	 */
	function createGuiEmbeddedEntriesFromEiuEntries(array $eiuEntries): array {
		ArgUtils::valArray($eiuEntries, EiuEntry::class);

		$guiEmbeddedEntries = [];
		foreach ($eiuEntries as $eiuEntry) {
			$guiEmbeddedEntries[] = $this->createGuiEmbeddedEntryFromEiuEntry($eiuEntry);
		}
		return $guiEmbeddedEntries;
	}

	function createGuiEmbeddedEntryFromEiuEntry(EiuEntry $eiuEntry): GuiEmbeddedEntry {
		return $this->create([$eiuEntry]);
	}

	function createNewGuiEmbeddedEntry(string $maskId): GuiEmbeddedEntry {
		return $this->create($this->eiuFrame->newPossibleEntries());
	}

	/**
	 * @param EiuEntry[] $eiuEntries
	 * @return GuiEmbeddedEntry
	 */
	private function create(array $eiuEntries): GuiEmbeddedEntry {
		$bulkyGui = $this->eiu->f()->gui()->createBulkyGui($eiuEntries, false);
		$summaryGui = null;
		if ($this->summaryRequired) {
			$summaryGui = $this->eiu->f()->gui()->createCompactGui($eiuEntries, true);
		}

		$guiEmbeddedEntry = new GuiEmbeddedEntry($bulkyGui, $summaryGui);

		foreach ($eiuEntries as $eiuEntry) {
			$eiuEntry->onValidate(fn () => $this->eiuEntriesMap->offsetSet($guiEmbeddedEntry, $eiuEntry));
		}
	}

	/**
	 * @param array $guiEmbeddedEntries
	 * @return EiuEntry[]
	 */
	function retrieveEiuEntries(array $guiEmbeddedEntries): array {
		return array_map(fn (GuiEmbeddedEntry $e) => $this->retrieveEiuEntry($e), $guiEmbeddedEntries);
	}

	function retrieveEiuEntry(GuiEmbeddedEntry $guiEmbeddedEntry): EiuEntry {
		$eiuEntry = $this->eiuEntriesMap->offsetGet($guiEmbeddedEntry);
		if ($eiuEntry === null) {
			throw new IllegalStateException();
		}
		return $eiuEntry;
	}
}