<?php

namespace rocket\impl\cu\gui\field\string;

use rocket\op\cu\gui\field\CuField;
use rocket\si\content\impl\StringInSiField;
use n2n\bind\build\impl\Bind;
use n2n\bind\mapper\impl\Mappers;
use n2n\core\container\N2nContext;

class StringInCuField implements CuField {

	private array $messageStrs = [];

	function __construct(private StringInSiField $siField) {
		$this->siField->setMessagesCallback(fn () => $this->messageStrs);
	}

	function setValue(?string $value): static {
		$this->messageStrs = [];
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): ?string {
		return $this->siField->getValue();
	}

	function getSiField(): StringInSiField {
		return $this->siField;
	}

	function validate(N2nContext $n2nContext): bool {
		$validationResult = Bind::values($this->getValue())->toClosure(fn ($v) => $this->setValue($v))
				->map(Mappers::cleanString($this->siField->isMandatory(), $this->siField->getMinlength(),
						$this->siField->getMaxlength()))
				->exec($n2nContext);

		if ($validationResult->hasErrors()) {
			$this->messageStrs = $validationResult->getErrorMap()->tAllMessages($n2nContext->getN2nLocale());
			return false;
		}

		return true;
	}

}