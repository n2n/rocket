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
use n2n\io\managed\FileManager;

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
	 * @param File $file
	 * @return \rocket\si\content\impl\FileInSiField
	 */
	static function fileIn(?File $file, Url $apiUrl, FileManager $fileManager) {
		return new FileInSiField($file, $apiUrl, $fileManager);
	}
	
	/**
	 * @param File $file
	 * @return \rocket\si\content\impl\FileOutSiField
	 */
	static function fileOut(?File $file) {
		return new FileOutSiField($file);
	}
	
	static function apiSelectIn(Url $apiUrl, array $values = [], int $min = 0, int $max = null) {
		return (new QualifierSelectInSiField($apiUrl, $values))->setMin($min)->setMax($max);
	}
}