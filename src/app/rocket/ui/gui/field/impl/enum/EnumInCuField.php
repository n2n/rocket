<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ui\gui\field\impl\enum;

use rocket\op\cu\gui\field\CuField;
use n2n\core\container\N2nContext;
use n2n\validation\build\impl\Validate;
use n2n\validation\validator\impl\Validators;
use rocket\ui\si\content\impl\EnumInSiField;
use rocket\ui\si\content\SiFieldModel;

class EnumInCuField implements CuField, SiFieldModel {
	private array $messageStrs = [];

	function __construct(private readonly EnumInSiField $siField) {
		$this->siField->setModel($this);
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

	function handleInput(): bool {
		return true;
	}

	function getMessageStrs(): array {
		return $this->messageStrs;
	}
}
