<?php

namespace rocket\cu\gui\field\impl\number;

use rocket\cu\gui\field\CuField;
use rocket\si\content\impl\NumberInSiField;

class NumberInCuField implements CuField {

	function __construct(private NumberInSiField $siField) {

	}

	function setValue(?string $value): static {
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?string {
		return $this->getValue();
	}

	function getSiField(): NumberInSiField {
		return $this->siField;
	}
}