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

use n2n\io\managed\File;

class FileOutSiField extends OutSiFieldAdapter {
	/**
	 * @var File|null
	 */
	private ?File $value = null;

	/**
	 * @param File|null $value
	 * @param SiFileHandler $siFileHandler
	 */
	function __construct(?File $value, private SiFileHandler $siFileHandler) {
		$this->value = $value;	
	}

	/**
	 * @param File|null $value
	 * @return FileOutSiField
	 */
	function setValue(?File $value): static {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return File|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'file-out';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'value' => ($this->value === null ? null : $this->siFileHandler->createSiFile($this->value, $n2nContext)),
			...parent::toJsonStruct($n2nContext)
		];
	}
}
