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
use n2n\util\uri\Url;
use n2n\util\type\attrs\DataSet;
use n2n\util\io\IoUtils;
use n2n\util\type\ArgUtils;
use n2n\io\managed\img\ThumbCut;
use n2n\util\type\attrs\DataMap;
use n2n\web\http\UploadDefinition;
use n2n\core\container\N2nContext;

class FileInSiField extends InSiFieldAdapter {

	private ?SiFile $value;
	/**
	 * @var Url
	 */
	private $apiFieldUrl;
	
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
	/**
	 * @var int|null
	 */
	private $maxSize = null;
	
	/**
	 * @var string[]
	 */
	private $acceptedExtensions = [];
	/**
	 * @var string[]
	 */
	private $acceptedMimeTypes = [];

	/**
	 * @param File|null $value
	 */
	function __construct(?SiFile $value, SiFileHandler $fileHandler) {
		$this->value = $value;
		$this->fileHandler = $fileHandler;
	}

	function setFileHandler(SiFileHandler $fileHandler): static {
		$this->fileHandler = $fileHandler;
		return $this;
	}

	function getFileHandler(): SiFileHandler {
		return $this->fileHandler;
	}

	function setValue(?SiFile $value): static {
		$this->value = $value;
		return $this;
	}

	function getValue(): ?SiFile {
		return $this->value;
	}

	function setMandatory(bool $mandatory): static {
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
	 * @return int|null
	 */
	public function getMaxSize() {
		return $this->maxSize;
	}
	
	/**
	 * @param int|null $maxSize
	 * @return FileInSiField
	 */
	public function setMaxSize(?int $maxSize) {
		$this->maxSize = $maxSize;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	public function getAcceptedExtensions() {
		return $this->acceptedExtensions;
	}
	
	/**
	 * @param string[] $acceptedExtensions
	 * @return FileInSiField
	 */
	public function setAcceptedExtensions(array $acceptedExtensions) {
		ArgUtils::valArray($acceptedExtensions, 'string');
		$this->acceptedExtensions = $acceptedExtensions;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	public function getAcceptedMimeTypes() {
		return $this->acceptedMimeTypes;
	}
	
	/**
	 * @param string[] $acceptedMimeTypes
	 * @return FileInSiField
	 */
	public function setAcceptedMimeTypes(array $acceptedMimeTypes) {
		ArgUtils::valArray($acceptedMimeTypes, 'string');
		$this->acceptedMimeTypes = $acceptedMimeTypes;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'file-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(N2nContext $n2nContext): array {
		return [
			'value' => ($this->value === null ? null : $this->fileHandler->createSiFile($this->value, $n2nContext)),
			'mandatory' => $this->mandatory,
//			'apiFieldUrl' => (string) $this->apiFieldUrl,
//			'apiCallId' => $this->apiCallId,
			'maxSize' => $this->maxSize ?? IoUtils::determineFileUploadMaxSize(),
			'acceptedExtensions' => $this->acceptedExtensions,
			'acceptedMimeTypes' => $this->acceptedMimeTypes,
			...parent::toJsonStruct($n2nContext)
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data): bool {
		$valueId = (new DataSet($data))->optArray('valueId', null, [], true);
		if ($valueId === null) {
			$this->value = null;
			return true;
		}

		$this->value = $this->fileHandler->getSiFileByRawId($valueId);
		if ($this->value === null || !isset($data['imageCuts'])) {
			return true;
		}
		
		foreach ($this->value->getImageDimensions() as $imgDim) {
			$id = $imgDim->getId();
			
			if (!isset($data['imageCuts'][$id])) {
				return true;
			}
			
			$imgDim->setThumbCut(ThumbCut::fromArray($data['imageCuts'][$id]));
		}

		return true;
	}
	
	function isCallable(): bool {
		return true;
	}
	
	function handleCall(array $data, array $uploadDefinitions, N2nContext $n2nContext): array {
		if (empty($uploadDefinitions)) {
			$this->setValue(null);
			return [];
		}
		
		/**
		 * @var UploadDefinition $uploadDefinition
		 */
		$uploadDefinition = current($uploadDefinitions);
		
		$dm = new DataMap($data);
		if (null !== ($fileName = $dm->optString('fileName'))) {
			$uploadDefinition->setName($fileName);
		}
		
		$uploadResult = $this->fileHandler->upload($uploadDefinition, $n2nContext);
		
		if (!$uploadResult->isSuccess()) {
			return ['error' => $uploadResult->getErrorMessage()];
		}
		
		$siFile = $uploadResult->getSiFile();
		$this->setValue($siFile);
		return ['file' => $siFile];
	}
}