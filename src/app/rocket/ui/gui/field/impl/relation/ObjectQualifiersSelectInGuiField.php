<?php

namespace rocket\ui\gui\field\impl\relation;

use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\impl\relation\ObjectQualifiersSelectInSiField;
use n2n\validation\validator\impl\Validators;

class ObjectQualifiersSelectInGuiField extends InGuiFieldAdapter {
	function __construct(private readonly ObjectQualifiersSelectInSiField $siField) {
		parent::__construct($this->siField);
	}

	function setValue(?array $value): static {
		$this->siField->setValue($value);
		return $this;
	}

	function getValue(): array {
		return $this->siField->getValue();
	}

	function getSiField(): ObjectQualifiersSelectInSiField {
		return $this->siField;
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		$mappers = [Validators::minElements($this->siField->getMin())];

		if (null !== ($max = $this->siField->getMax())) {
			$mappers[] = Validators::maxElements($max);
		}

		return $mappers;
	}
}