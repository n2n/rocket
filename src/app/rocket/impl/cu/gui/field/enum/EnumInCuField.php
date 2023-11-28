<?php
namespace rocket\impl\cu\gui\field\enum;

use rocket\op\cu\gui\field\CuField;
use n2n\core\container\N2nContext;
use n2n\validation\build\impl\Validate;
use n2n\validation\validator\impl\Validators;
use rocket\si\content\impl\EnumInSiField;

class EnumInCuField implements CuField {
	private array $messageStrs = [];

	function __construct(private readonly EnumInSiField $siField) {
		$this->siField->setMessagesCallback(fn () => $this->messageStrs);
	}

	function setValue(?string $value): static {
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?string {
		return $this->siField->getValue();
	}

	function getSiField(): EnumInSiField {
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
}
