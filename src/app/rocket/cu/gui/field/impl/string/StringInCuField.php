<?php

namespace rocket\cu\gui\field\impl\string;

use rocket\cu\gui\field\CuField;
use rocket\si\content\impl\StringInSiField;
use n2n\bind\build\impl\Bind;
use n2n\bind\mapper\impl\Mappers;
use n2n\validation\validator\impl\Validators;

class StringInCuField implements CuField {


	function __construct(private StringInSiField $siField) {

	}

	function setValue(?string $value): static {
		$this->value = $value;
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?string {
		return $this->getValue();
	}

	function getSiField(): StringInSiField {
		return $this->siField;
	}

	function validate(): void {
		Bind::values($this->getValue())->toValue($this->value)
				->map(Mappers::cleanString($this->siField->isMandatory(), $this->siField->getMinlength(),
						$this->siField->getMaxlength()));
	}
}