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

	private \WeakMap $eiuEntriesItemsMap;

	function __construct(private EiuFrame $eiuFrame, private bool $summaryRequired) {
		$this->eiu = new Eiu($this->eiuFrame);
		$this->eiuEntriesItemsMap = new \WeakMap();
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

	function updateGuiEmbeddedEntrySummary(GuiEmbeddedEntry $guiEmbeddedEntry): void {
		if (!$this->summaryRequired) {
			return;
		}

		$item = $this->retrieveEiuEntriesItem($guiEmbeddedEntry);
		$guiEmbeddedEntry->setSummaryGui($this->eiu->f()->gui()->createCompactGui($item->eiuEntries, true));
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
		$eiuEntriesItem = new EiuEntriesItem($eiuEntries);
		$this->eiuEntriesItemsMap->offsetSet($guiEmbeddedEntry, $eiuEntriesItem);

		foreach ($eiuEntries as $eiuEntry) {
			$eiuEntry->onValidate(fn () => $eiuEntriesItem->validatedEiuEntry = $eiuEntry);
			$eiuEntry->whenValidated(fn () => $this->updateGuiEmbeddedEntrySummary($guiEmbeddedEntry));
		}

		return $guiEmbeddedEntry;
	}

	function retrieveEiuEntriesItem(GuiEmbeddedEntry $guiEmbeddedEntry): EiuEntriesItem {
		$item = $this->eiuEntriesItemsMap->offsetGet($guiEmbeddedEntry);
		if ($item !== null) {
			return $item;
		}

		throw new IllegalStateException();
	}

	/**
	 * @param array $guiEmbeddedEntries
	 * @return EiuEntry[]
	 */
	function retrieveValidatedEiuEntries(array $guiEmbeddedEntries): array {
		return array_map(fn (GuiEmbeddedEntry $e) => $this->retrieveValidatedEiuEntry($e), $guiEmbeddedEntries);
	}

	function retrieveValidatedEiuEntry(GuiEmbeddedEntry $guiEmbeddedEntry): EiuEntry {
		$eiuEntry = $this->retrieveEiuEntriesItem($guiEmbeddedEntry)->validatedEiuEntry;
		if ($eiuEntry === null) {
			throw new IllegalStateException();
		}
		return $eiuEntry;
	}
}

class EiuEntriesItem {
	/**
	 * @param  EiuEntry[] $eiuEntries
	 */
	function __construct(public array $eiuEntries) {
	}

	public ?EiuEntry $validatedEiuEntry;
}