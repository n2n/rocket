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
namespace rocket\ui\si\content\impl;

class StringOutSiField extends OutSiFieldAdapter {
	private $value;
	private $multiline = false;
	
	function __construct(?string $value) {
		$this->value = $value;
	}
	
	/**
	 * @return string
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\StringOutSiField
	 */
	function setValue(?string $value) {
		$this->value = $value;;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMultiline(): bool {
		return $this->multiline;
	}
	

	function setMultiline(bool $multiline): static {
		$this->multiline = $multiline;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'string-out';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'value' => $this->value,
			'multiline' => $this->multiline,
			...parent::toJsonStruct($n2nContext)
		];
	}
}
