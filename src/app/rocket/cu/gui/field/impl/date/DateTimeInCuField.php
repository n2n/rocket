<?php

namespace rocket\cu\gui\field\impl\date;

use rocket\cu\gui\field\CuField;
use rocket\si\content\impl\date\DateTimeInSiField;

class DateTimeInCuField implements CuField {

	function __construct(private readonly DateTimeInSiField $siField) {
	}

	function setValue(?string $value): static {
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?string {
		return $this->getValue();
	}

	function getSiField(): DateTimeInSiField {
		return $this->siField;
	}

}