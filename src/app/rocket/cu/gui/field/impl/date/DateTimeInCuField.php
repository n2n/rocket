<?php

namespace rocket\cu\gui\field\impl\date;

use rocket\cu\gui\field\CuField;
use rocket\si\content\impl\date\DateTimeInSiField;
use n2n\core\container\N2nContext;
use n2n\validation\build\impl\Validate;
use n2n\validation\validator\impl\Validators;

class DateTimeInCuField implements CuField {
	private $messageStrs = [];

	function __construct(private readonly DateTimeInSiField $siField) {
		$this->siField->setMessagesCallback(fn () => $this->messageStrs);
	}

	function setValue(?\DateTime $value): static {
		$this->messageStrs = [];
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?\DateTime {
		return $this->siField->getValue();
	}

	function getSiField(): DateTimeInSiField {
		return $this->siField;
	}

	function validate(N2nContext $n2nContext): bool {
		$this->messageStrs = [];

		if (!$this->siField->isMandatory()) {
			return true;
		}

		$validationResult = Validate::value($this->getValue())
				->val(Validators::mandatory())
				->exec($n2nContext);

		if ($validationResult->hasErrors()) {
			$this->messageStrs = $validationResult->getErrorMap()->tAllMessages($n2nContext->getN2nLocale());
			return false;
		}

		return true;
	}
}