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
namespace rocket\si\input;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use n2n\web\http\UploadDefinition;
use n2n\io\managed\impl\FileFactory;
use n2n\util\type\ArgUtils;
use n2n\util\StringUtils;
use n2n\util\JsonDecodeFailedException;
use n2n\io\managed\File;
use n2n\l10n\Message;
use n2n\util\ex\IllegalStateException;

class SiInputFactory {
	
	/**
	 * @var File[]
	 */
	private $fileMap = [];
	
	/**
	 * @var SiEntryInputError[]
	 */
	private $entryErrors = [];
	
	/**
	 * @param UploadDefinition[] $uploadDefinitions key must be http param name
	 */
	function registerUploadDefinitions(array $uploadDefinitions) {
		ArgUtils::valArray($uploadDefinitions, UploadDefinition::class);
		
		foreach ($uploadDefinitions as $key => $uploadDefinition) {
			$this->registerUploadDefinition($key, $uploadDefinition);
		}
	}
	
	/**
	 * @return boolean
	 */
	function hasErrors() {
		return !empty($this->entryErrors);
	}
	
	/**
	 * @return \rocket\si\input\SiEntryInputError
	 */
	function createInputError() {
		if ($this->hasErrors()) {
			return new SiInputError($this->entryErrors);
		}
		
		throw new IllegalStateException('No errors.');
	}
	
	/**
	 * @param string $key
	 * @param UploadDefinition $uploadDefinition
	 * @throws CorruptedSiInputDataException
	 */
	private function registerUploadDefinition($key, $uploadDefinition) {
		$entryKey = null;
		$fieldName = null;
		$dataKey = null;
		
		try {
			$ds = new DataSet(StringUtils::jsonDecode(urldecode($json)));
			$entryKey = $ds->reqInt(0);
			$fieldName = $ds->reqString(1);
			$dataKey = $ds->reqString(2);
		} catch (JsonDecodeFailedException $e) {
			throw new CorruptedSiInputDataException(null, 0, $e);
		}
		
		if ($uploadDefinition->hasClientError()) {
			$this->registerMessage($entryKey, $fieldName, $uploadDefinition->buildClientErrorMessage());
			continue;
		}
		
		$this->registerFile($entryKey, $fieldName, $dataKey,
				FileFactory::createFromUploadDefinition($uploadDefinition));
	}
	
	/**
	 * @param int $entryKey
	 * @param string $fieldName
	 * @param string $datKey
	 * @param File $file
	 */
	private function registerFile($entryKey, $fieldName, $dataKey, $file) {
		if (!isset($this->fileMap[$entryKey])) {
			$this->fileMap[$entryKey] = [];
		}
		
		if (!isset($this->fileMap[$entryKey][$fieldName])) {
			$this->fileMap[$entryKey][$fieldName] = [];
		}
		
		$this->fileMap[$entryKey][$fieldName][$dataKey] = $file;
	}
	
	/**
	 * @param int $entryKey
	 * @param string $fieldName
	 * @param Message $message
	 */
	private function registerMessage($entryKey, $fieldName, $message) {
		if (!isset($this->entryErrors[$entryKey])) {
			$this->entryErrors[$entryKey] = new SiEntryInputError();
		}
			
		$this->entryErrors[$entryKey]->registerError($fieldName, $message);
	}
	
	/**
	 * @param array $data
	 * @return SiInput
	 * @throws CorruptedSiInputDataException
	 */
	function create(array $data) {
		if ($this->hasErrors()) {
			throw new IllegalStateException('Can not create with upload errors.');
		}
		
		$input = new SiInput();
		
		foreach ($data as $key => $entryData) {
			try {
				$input->addEntryInput($this->createEntry($entryKey, $entryData));
			} catch (AttributesException $e) {
				throw new CorruptedSiInputDataException(null, 0, $e);
			}
		}
		
		return $input;
	}
	
	/**
	 * @param int $entryKey
	 * @param array $data
	 * @return SiEntryInput
	 */
	private function createEntry($entryKey, $data) {
		$dataSet = new DataSet($data);
		
		$siEntryInput = new SiEntryInput($dataSet->reqString('category'), $dataSet->reqString('buildupId'), 
				$dataSet->optString('id'));
		foreach ($dataSet->reqArray('fieldInputMap', 'array') as $fieldId => $fielData) {
			$siEntryInput->setFieldInput($fieldId, $this->createField($entryKey, $fieldId, $fielData));
		}
		return $siEntryInput;
	}
	
	/**
	 * @param array $data
	 * @return \rocket\si\input\SiFieldInput
	 */
	private function createField($entryKey, $fieldName, $data) {
		if (isset($this->fileMap[$entryKey][$fieldName])) {
			$data = $this->fileMap[$entryKey][$fieldName] + $data;
		}
		
		return new SiFieldInput($data);
	}
}

class CorruptedSiInputDataException extends \Exception {
	
}
