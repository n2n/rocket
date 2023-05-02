<?php

namespace rocket\cu\gui;

use rocket\cu\gui\field\CuField;
use rocket\si\content\SiEntry;

class CuGuiEntry {
	/**
	 * @var array
	 */
	private array $cuFields = [];

	function containsCuField(string $id): bool {
		return isset($this->cuFields[$id]);
	}

	function putCuField(string $id, CuField $cuField): static {
		$this->cuFields[$id] = $cuField;
		return $this;
	}

	function toSiEntry(): SiEntry {
		$siEntry = new SiEntry();
		foreach ($this->cuFields as $id => $cuField) {
			$siEntry->putField($id, $cuField);
		}
		return $siEntry;
	}
}