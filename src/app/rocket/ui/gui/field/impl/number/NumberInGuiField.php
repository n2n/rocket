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
namespace rocket\ui\gui\field\impl\number;

use rocket\op\cu\gui\field\CuField;
use rocket\ui\si\content\impl\NumberInSiField;
use n2n\core\container\N2nContext;
use n2n\validation\build\impl\Validate;
use n2n\validation\validator\impl\Validators;
use rocket\ui\si\content\SiFieldModel;
use rocket\ui\gui\field\impl\InGuiFieldAdapter;
use n2n\bind\mapper\impl\Mappers;

class NumberInGuiField extends InGuiFieldAdapter {


	function __construct(private readonly NumberInSiField $siField) {
		parent::__construct($this->siField);
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

	function createInputMappers(N2nContext $n2nContext): array {
		return [Mappers::float(mandatory: $this->siField->isMandatory(), min: $this->siField->getMin(),
				max: $this->siField->getMax(), step: $this->siField->getStep())];
	}
}