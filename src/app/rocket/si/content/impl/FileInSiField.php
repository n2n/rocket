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
use rocket\ei\manage\gui\field\GuiFieldPath;

class FileInSiField extends InSiFieldAdapter {
	/**
	 * @var File|null
	 */
	private $value;
	/**
	 * @var Url
	 */
	private $apiUrl;
	
	/**
	 * @var GuiFieldPath
	 */
	private $guiFieldPath;
	/**
	 * @var FileManager
	 */
	private $fileManager;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	
	private $extensions = [];
	private $mimeTypes = [];
		
	/**
	 * @param File|null $value
	 */
	function __construct(?File $value, Url $apiUrl, GuiFieldPath $guiFieldPath, FileManager $fileManager) {
		$this->value = $value;	
		$this->apiUrl = $apiUrl;
		$this->guiFieldPath = $guiFieldPath;
		$this->fileManager = $fileManager;
	
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setValue(?File $value) {
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
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMandatory() {
		return $this->mandatory;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'file-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => SiFile::build($this->value),
			'mandatory' => $this->mandatory,
			'mimeTypes' => $this->mimeTypes,
			'extensions' => $this->extensions,
			'apiUrl' => (string) $this->apiUrl,
			'fieldId' => (string) $this->guiFieldPath
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
	}
}