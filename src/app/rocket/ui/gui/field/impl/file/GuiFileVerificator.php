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

use n2n\l10n\Message;
use n2n\validation\lang\ValidationMessages;
use n2n\io\managed\File;
use n2n\io\managed\img\ImageFile;
use n2n\validation\validator\impl\ValidationUtils;
use rocket\ui\si\content\impl\FileInSiField;

class GuiFileVerificator {

	function __construct(private FileInSiField $siField, public bool $imageRecognized) {
		
	}
	
	public function test(File $file): bool {
		return $this->testSize($file) && $this->testType($file) && $this->testResolution($file);
	}
	
	
	public function validate(File $file): ?Message {
		if (!$this->testSize($file)) {
			return ValidationMessages::uploadMaxSize($this->siField->getMaxSize(), $file->getOriginalName(),
					$file->getFileSource()->getSize());
		}
		
		if (!$this->testType($file)) {
			return ValidationMessages::fileType($file, 
					array_merge($this->siField->getAcceptedExtensions(), $this->siField->getAcceptedMimeTypes()));
		}
		
		if (!$this->testResolution($file)) {
			return ValidationMessages::imageResolution($file->getOriginalName());
		}
		
		return null;
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	private function testType(File $file): bool {
		$allowedMimeTypes = $this->siField->getAcceptedMimeTypes();
		$allowedExtensions = $this->siField->getAcceptedExtensions();
		return ValidationUtils::isFileTypeSupported($file,
				(empty($allowedMimeTypes) ? null : $allowedMimeTypes),
				(empty($allowedExtensions) ? null : $allowedExtensions));
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	private function testSize(File $file): bool {
		$maxSize = $this->siField->getMaxSize();
		return  $maxSize === null || $file->getFileSource()->getSize() <= $maxSize;
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	private function testResolution(File $file): bool {
		return !$this->imageRecognized || !$file->getFileSource()->isImage()
				|| ValidationUtils::isImageResolutionManagable(new ImageFile($file));
	}
}