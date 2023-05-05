<?php

namespace rocket\cu\gui;

use rocket\cu\gui\field\CuField;
use rocket\si\content\SiEntry;
use phpbob\representation\ex\UnknownElementException;
use rocket\si\input\SiEntryInput;
use n2n\core\container\N2nContext;
use rocket\si\input\SiInputError;

class CuGuiEntry {
	/**
	 * @var array
	 */
	private array $cuFields = [];
	private SiEntry $siEntry;

	function __construct() {
		$this->siEntry = new SiEntry(null, null);
	}

	function containsCuField(string $id): bool {
		return isset($this->cuFields[$id]);
	}

	function putCuField(string $id, CuField $cuField): static {
		$this->siEntry->putField($id, $cuField->getSiField());
		$this->cuFields[$id] = $cuField;
		return $this;
	}

	function getCuField(string $id): CuField {
		if (isset($this->cuFields[$id])) {
			return $this->cuFields[$id];
		}

		throw new UnknownElementException('Unknown CuField id: ' . $id);
	}

	function getSiEntry(): SiEntry {
		return $this->siEntry;
	}

	function handleSiEntryInput(SiEntryInput $siEntryInput, N2nContext $n2nContext): bool {
		$this->siEntry->handleEntryInput($siEntryInput);

		$failed = false;

		foreach ($this->cuFields as $cuField) {
			if (!$cuField->readSi($siEntryInput, $n2nContext)) {
				$failed = true;
			}
		}

		return $failed;
	}
}