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
namespace rocket\ui\gui\field\impl\string;

use rocket\ui\si\content\impl\StringInSiField;
use n2n\bind\mapper\impl\Mappers;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\SiFieldModel;
use rocket\ui\gui\field\GuiField;
use n2n\bind\mapper\Mapper;
use rocket\ui\gui\field\impl\InGuiFieldAdapter;

class StringInGuiField extends InGuiFieldAdapter implements GuiField, SiFieldModel {

	private array $messageStrs = [];

	function __construct(private StringInSiField $siField) {
		$this->siField->setModel($this);
	}

	protected function createInputMappers(N2nContext $n2nContext): array {
		return [Mappers::cleanString($this->siField->isMandatory(), $this->siField->getMinlength(),
				$this->siField->getMaxlength(), $this->siField->isMultiline())];
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

//	protected function createInputMapper(N2nContext	$n2nContext): Mapper {
//		return Mappers::cleanString($this->siField->isMandatory(), $this->siField->getMinlength(),
//				$this->siField->getMaxlength());
//	}

	function getMessages(): array {
		return $this->messageStrs;
	}

}