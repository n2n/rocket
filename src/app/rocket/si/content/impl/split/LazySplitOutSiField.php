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
namespace rocket\si\content\impl\split;

use n2n\util\ex\IllegalStateException;
use rocket\si\content\impl\OutSiFieldAdapter;
use rocket\si\content\SiField;
use rocket\si\SiPayloadFactory;

class LazySplitOutSiField extends OutSiFieldAdapter {
	private $subFields = [];
	
	function __construct() {
	}
	
	/**
	 * @return SiField[]
	 */
	function getSubFields() {
		return $this->subFields;
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\StringOutSiField
	 */
	function putSubField(string $key, SiField $subField) {
		$this->subFields[$key] = $subField;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-out';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'subFields' => SiPayloadFactory::createDataFromFields($this->subFields)
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\impl\OutSiFieldAdapter::handleInput()
	 */
	function handleInput(array $data): array {
		throw new IllegalStateException();
	}
}
