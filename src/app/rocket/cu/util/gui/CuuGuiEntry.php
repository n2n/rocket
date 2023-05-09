<?php

namespace rocket\cu\util\gui;

use rocket\cu\gui\CuGuiEntry;
use rocket\cu\util\CuuAnalyst;

class CuuGuiEntry {

	function __construct(private readonly CuGuiEntry $cuGuiEntry, private CuuAnalyst $cuuAnalyst) {

	}

	function getCuGuiEntry(): CuGuiEntry {
		return $this->cuGuiEntry;
	}

	function getValue(string $fieldName): mixed {
		return $this->cuGuiEntry->getCuField($fieldName)->getValue();
	}
}