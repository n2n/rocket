<?php

namespace rocket\cu\gui;

use rocket\cu\gui\field\CuField;
use rocket\si\content\SiEntry;

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

	function getSiEntry(): SiEntry {
		return $this->siEntry;
	}
}