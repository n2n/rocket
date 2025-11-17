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
namespace rocket\ui\gui\field\impl\file;

use n2n\io\managed\File;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\err\CorruptedSiDataException;

class SiFileId implements \JsonSerializable {
	private $fileManagerName;
	private $qualifiedName;
	
	/**
	 * @param string|null $fileManagerName
	 * @param string|null $qualifiedName
	 */
	private function __construct(?string $fileManagerName, ?string $qualifiedName) {
		$this->fileManagerName = $fileManagerName;
		$this->qualifiedName = $qualifiedName;
	}
	
	/**
	 * @return string
	 */
	function getFileManagerName() {
		return $this->fileManagerName;
	}
	
	/**
	 * @return string
	 */
	function getQualifiedName() {
		return $this->qualifiedName;
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	function matches(File $file) {
		$fileSource = $file->getFileSource();
		return $fileSource->getFileManagerName() === $this->fileManagerName 
				&& $this->qualifiedName === $fileSource->getQualifiedName();
	}
	
	function jsonSerialize(): mixed {
		return [
			'fileManagerName' => $this->fileManagerName,
			'qualifiedName' => $this->qualifiedName
		];
	}

	/**
	 * @param array $data
	 * @return SiFileId
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiFileId {
		$ds = new DataSet($data);
		try {
			return new SiFileId($ds->optString('fileManagerName'), $ds->optString('qualifiedName'));
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException($e->getMessage(), 0, $e);
		}
	}
	
	/**
	 * @param File $file
	 * @return \rocket\ui\gui\field\impl\file\SiFileId
	 */
	static function create(File $file) {
		$fileSource = $file->getFileSource();
		return new SiFileId($fileSource->getFileManagerName(), $fileSource->getQualifiedName());
	}
}