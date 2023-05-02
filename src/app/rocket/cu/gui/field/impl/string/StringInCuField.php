<?php

namespace rocket\cu\gui\field\impl\string;

use rocket\cu\gui\field\CuField;
use rocket\si\content\impl\StringInSiField;

class StringInCuField implements CuField {

	function __construct(private StringInSiField $siField) {

	}

	function setValue(?string $value): static {
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?string {
		return $this->getValue();
	}

	function getSiField(): StringInSiField {
		return $this->siField;
	}
}