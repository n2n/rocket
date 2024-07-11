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
namespace rocket\ui\gui\field\impl\date;

use rocket\op\cu\gui\field\CuField;
use rocket\ui\si\content\impl\date\DateTimeInSiField;
use n2n\core\container\N2nContext;
use n2n\validation\build\impl\Validate;
use n2n\validation\validator\impl\Validators;
use rocket\ui\si\content\SiFieldModel;

class DateTimeInCuField implements CuField, SiFieldModel {

	private array $messageStrs = [];

	function __construct(private readonly DateTimeInSiField $siField) {
		$this->siField->setModel($this);
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

	function handleInput(): bool {
		return true;
	}

	function getMessageStrs(): array {
		return $this->messageStrs;
	}
}