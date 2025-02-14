<?php

namespace rocket\ui\si\content\impl\relation;

use n2n\util\type\ArgUtils;
use rocket\ui\si\api\request\SiValueBoundaryInput;
use n2n\core\container\N2nContext;
use rocket\ui\si\err\CorruptedSiDataException;

class SiEmbeddedEntriesCollection {

	/**
	 * @var SiEmbeddedEntry[] $embeddedEntries
	 */
	private array $embeddedEntries = [];

	function __construct(private SiEmbeddedEntryFactory $embeddedEntryFactory) {
	}

	function setEmbeddedEntries(array $embeddedEntries): static {
		ArgUtils::valArray($embeddedEntries, SiEmbeddedEntry::class);
		$this->embeddedEntries = $embeddedEntries;
		return $this;
	}

	function addEmbeddedEntry(SiEmbeddedEntry $embeddedEntry): static {
		$this->embeddedEntries[] = $embeddedEntry;
		return $this;
	}

	function getEmbeddedEntries(): array {
		return $this->embeddedEntries;
	}

	private function findExisting(string $maskId, string $entryId): ?SiEmbeddedEntry {
		foreach ($this->embeddedEntries as $embeddedEntry) {
			if ($embeddedEntry->getContent()->getValueBoundary()->containsEntryWith($maskId, $entryId)) {
				return $embeddedEntry;
			}
		}

		return null;
	}

	/**
	 * @param SiValueBoundaryInput[] $valueBoundaryInputs
	 * @param N2nContext $n2nContext
	 * @return bool
	 * @throws CorruptedSiDataException
	 */
	function handleInput(array $valueBoundaryInputs, N2nContext $n2nContext): bool {
		ArgUtils::valArray($valueBoundaryInputs, SiValueBoundaryInput::class);

		$valid = true;
		$embeddedEntries = [];
		foreach ($valueBoundaryInputs as $valueBoundaryInput) {
			$entryInput = $valueBoundaryInput->getEntryInput();
			$maskId = $entryInput->getMaskId();
			$entryId = $entryInput->getEntryId();

			if ($entryId !== null) {
				$siEmbeddedEntry = $this->findExisting($maskId, $entryId);
			} else {
				$siEmbeddedEntry = $this->embeddedEntryFactory->createSiEmbeddedEntry($entryInput->getMaskId(), $entryInput->getEntryId());
			}

			if ($siEmbeddedEntry === null) {
				continue;
			}

			if (!$siEmbeddedEntry->getContent()->getValueBoundary()->handleInput($valueBoundaryInput, $n2nContext)) {
				$valid = false;
			}
			$embeddedEntries[] = $siEmbeddedEntry;
		}

		$this->embeddedEntries = $embeddedEntries;
		return $valid;
	}
}
