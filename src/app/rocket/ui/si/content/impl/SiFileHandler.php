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

use n2n\web\http\UploadDefinition;
use n2n\core\container\N2nContext;
use n2n\io\managed\File;
use rocket\ui\si\err\CorruptedSiDataException;

interface SiFileHandler {

	function upload(UploadDefinition $uploadDefinition, N2nContext $n2nContext): SiUploadResult;
	
	/**
	 * @param array $fileId
	 * @param File|null $currentValue
	 * @param N2nContext $n2nContext
	 * @return File|null $currentValue
 	 * @throws CorruptedSiDataException if id is corrupted
	 */
	function determineFileByRawId(array $fileId, ?File $currentValue, N2nContext $n2nContext): ?File;

	function createSiFile(File $file, N2nContext $n2nContext): SiFile;
}

class SiUploadResult {
	/**
	 * @var File|null
	 */
	private $file;
	/**
	 * @var string|null
	 */
	private $errorMessage;
	
	/**
	 * @param File|null $file
	 * @param string|null $errorMessage
	 */
	private function __construct($file, $errorMessage) {
		$this->file = $file;
		$this->errorMessage = $errorMessage;
	}
	
	/**
	 * @return boolean
	 */
	function isSuccess() {
		return $this->file !== null;
	}
	

	function getFile(): ?File {
		return $this->file;
	}
	
	/**
	 * @return string|null
	 */
	function getErrorMessage() {
		return $this->errorMessage;
	}
	
	/**
	 * @param File $file
	 * @return \rocket\ui\si\content\impl\SiUploadResult
	 */
	static function createSuccess(File $file) {
		return new SiUploadResult($file, null);
	}
	
	/**
	 * @param string $errorMessage
	 * @return \rocket\ui\si\content\impl\SiUploadResult
	 */
	static function createError(string $errorMessage) {
		return new SiUploadResult(null, $errorMessage);
	}
}
	