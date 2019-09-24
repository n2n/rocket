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
namespace rocket\si\content\impl;

use n2n\io\managed\File;
use n2n\util\uri\Url;
use rocket\si\content\impl\relation\QualifierSelectInSiField;
use rocket\si\content\impl\relation\EmbeddedEntryInSiField;
use rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField;
use rocket\si\content\impl\relation\EmbeddedEntryPanelInputHandler;
use rocket\si\content\impl\relation\EmbeddedEntryInputHandler;

class SiFields {
	
	/**
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	static function stringIn(?string $value) {
		return new StringInSiField($value);
	}
	
	/**
	 * @return \rocket\si\content\impl\StringOutSiField
	 */
	static function stringOut(?string $value) {
		return new StringOutSiField($value);
	}
	
	/**
	 * @param int|null $value
	 * @return \rocket\si\content\impl\IntInSiField
	 */
	static function intIn(?int $value) {
		return new IntInSiField($value);
	}
	
	/**
	 * @param bool $value
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	static function boolIn(bool $value) {
		return new BoolInSiField($value);
	}
	
	/**
	 * @param string[] $options
	 * @param string $value
	 * @return \rocket\si\content\impl\EnumInSiField
	 */
	static function enumIn(array $options, ?string $value) {
		return new EnumInSiField($options, $value);
	}
	
	/**
	 * @param SiFile|null $file
	 * @return \rocket\si\content\impl\FileInSiField
	 */
	static function fileIn(?SiFile $file, Url $apiUrl, \JsonSerializable $apiCallId, SiFileHandler $fileHandle) {
		return new FileInSiField($file, $apiUrl, $apiCallId, $fileHandle);
	}
	
	/**
	 * @param File $file
	 * @return \rocket\si\content\impl\FileOutSiField
	 */
	static function fileOut(?SiFile $file) {
		return new FileOutSiField($file);
	}
	
	/**
	 * @param Url $ref
	 * @param string $label
	 * @param bool $href
	 * @return \rocket\si\content\impl\LinkOutSiField
	 */
	static function linkOut(Url $ref, string $label, bool $href) {
		return new LinkOutSiField($ref, $label, $href);
	}
	
	/**
	 * @param Url $apiUrl
	 * @param array $values
	 * @param int $min
	 * @param int|null $max
	 * @return QualifierSelectInSiField
	 */
	static function qualifierSelectIn(Url $apiUrl, array $values = [], int $min = 0, int $max = null) {
		return (new QualifierSelectInSiField($apiUrl, $values))->setMin($min)->setMax($max);
	}
	
	/**
	 * @param Url $apiUrl
	 * @param array $values
	 * @param array $summarySiContents
	 * @param int $min
	 * @param int $max
	 * @return EmbeddedEntryInSiField
	 */
	static function embeddedEntryIn(Url $apiUrl, EmbeddedEntryInputHandler $inputHandler, array $values = [], 
			int $min = 0, int $max = null) {
		return (new EmbeddedEntryInSiField($apiUrl, $inputHandler, $values))->setMin($min)->setMax($max);
	}
	
	/**
	 * @param Url $apiUrl
	 * @param EmbeddedEntryPanelInputHandler $inputHandler
	 * @param array $panels
	 * @return \rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField
	 */
	static function embeddedEntryPanelsIn(Url $apiUrl, EmbeddedEntryPanelInputHandler $inputHandler, 
			array $panels = []) {
		return (new EmbeddedEntryPanelsInSiField($apiUrl, $inputHandler, $panels));
	}
}