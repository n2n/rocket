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
	 * @var \JsonSerializable
	 */
	private $apiCallId;
	/**
	 * @var SiFileHandler
	 */
	private $fileHandler;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	
	private $extensions = [];
	private $mimeTypes = [];
		
	/**
	 * @param File|null $value
	 */
	function __construct(?SiFile $value, Url $apiUrl, \JsonSerializable $apiCallId, SiFileHandler $fileHandler) {
		$this->value = $value;	
		$this->apiUrl = $apiUrl;
		$this->apiCallId = $apiCallId;
		$this->fileHandler = $fileHandler;
	}
	
	/**
	 * @param SiFile|null $value
	 * @return \rocket\si\content\impl\FileInSiField
	 */
	function setValue(?SiFile $value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return SiFile|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\FileInSiField
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
			'value' => $this->value,
			'mandatory' => $this->mandatory,
			'mimeTypes' => $this->mimeTypes,
			'extensions' => $this->extensions,
			'apiUrl' => (string) $this->apiUrl,
			'apiCallId' => $this->apiCallId
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		
	}
	
	function isCallable(): bool {
		return true;
	}
	
	function handleCall(array $data, array $uploadDefinitions): array {
		if (empty($uploadDefinitions)) {
			$this->setValue(null);
			return [];
		}
		
		/**
		 * @var UploadDefinition $uploadDefinition
		 */
		$uploadDefinition = current($uploadDefinitions);
		$uploadResult = $this->fileHandler->upload($uploadDefinition);
		
		if (!$uploadResult->isSuccess()) {
			return ['error' => $uploadResult->getErrorMessage()];
		}
		
		$siFile = $uploadResult->getSiFile();
		$this->setValue($siFile);
		return ['file' => $siFile];
	}
}