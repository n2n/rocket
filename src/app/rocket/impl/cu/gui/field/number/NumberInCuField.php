<?php

namespace rocket\impl\cu\gui\field\number;

use rocket\op\cu\gui\field\CuField;
use rocket\si\content\impl\NumberInSiField;
use n2n\core\container\N2nContext;
use n2n\validation\build\impl\Validate;
use n2n\validation\validator\impl\Validators;
use rocket\si\content\SiFieldModel;

class NumberInCuField implements CuField, SiFieldModel {

	private array $messageStrs = [];

	function __construct(private readonly NumberInSiField $siField) {
		$this->siField->setModel($this);
	}

	function setValue(?float $value): static {
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?float {
		return $this->siField->getValue();
	}

	function getSiField(): NumberInSiField {
		return $this->siField;
	}

	function validate(N2nContext $n2nContext): bool {
		$val = Validate::value($this->getValue());

		if ($this->siField->isMandatory()) {
			$val->val(Validators::mandatory());
		}

		$validationResult = $val->exec($n2nContext);

		if (!$validationResult->hasErrors()) {
			return true;
		}

		$this->messageStrs = $validationResult->getErrorMap()->tAllMessages($n2nContext->getN2nLocale());
		return false;
	}

	function handleInput(): bool {
		return true;
	}

	function getMessageStrs(): array {
		return $this->messageStrs;
	}
}