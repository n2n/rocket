<?php

namespace rocket\cu\gui\field\impl\number;

use rocket\cu\gui\field\CuField;
use rocket\si\content\impl\NumberInSiField;
use n2n\core\container\N2nContext;
use n2n\bind\build\impl\Bind;
use n2n\bind\mapper\impl\Mappers;

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

	function validate(N2nContext $n2nContext): bool {
		return true;
//		$validationResult = Bind::values($this->getValue())->toClosure(fn ($v) => $this->setValue($v))
//				->map(Mappers::($this->siField->isMandatory(), $this->siField->getMin(), $this->siField->getMax()))
//				->exec($n2nContext);
//
//		$this->messageStrs = $validationResult->getErrorMap()->tAllMessages($n2nContext->getN2nLocale());
//
//		return $validationResult->hasErrors();
	}
}