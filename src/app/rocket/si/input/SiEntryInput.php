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

use rocket\si\content\SiIdentifier;
use n2n\util\type\attrs\DataSet;

class SiEntryInput {
	/**
	 * @var SiIdentifier
	 */
	private $identifier;
	/**
	 * @var string
	 */
	private $buildupId;
	/**
	 * @var SiFieldInput[]
	 */
	private $fieldInputs = [];
	
	/**
	 * @param string $category
	 * @param string $buildupId
	 * @param string $id
	 */
	function __construct(SiIdentifier $identifier, string $buildupId, string $id) {
		$this->identifier = $identifier;
		$this->buildupId = $buildupId;
		$this->id = $id;
	}
	
	/**
	 * @return SiIdentifier
	 */
	function getSiIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * @return string
	 */
	function getBuildupId() {
		return $this->buildupId;
	}
	
	/**
	 * @param string $fieldId
	 * @param SiFieldInput $fieldInput
	 */
	function putFieldInput(string $fieldName, SiFieldInput $fieldInput) {
		$this->fieldInputs[$fieldName] = $fieldInput;
	}
	
	/**
	 * @param string $fieldName
	 * @return bool
	 */
	function containsFieldName(string $fieldName) {
		return isset($this->fieldInputs[$fieldName]);
	}
	
	function getFieldInput(string $fieldName) {
		if (isset($this->fieldInputs[$fieldName])) {
			return $this->fieldInputs[$fieldName];
		}
		
		throw new \OutOfBoundsException('Unknown field name: ' . $fieldName);
	}
	
	/**
	 * @return SiFieldInput[]
	 */
	function getFieldInputs() {
		return $this->fieldInputs;
	}
	
	/**
	 * @param array $data
	 * @return SiEntryInput
	 * @throws CorruptedSiInputDataException
	 */
	static function parse(array $data) {
		$dataSet = new DataSet($data);
		
		try {
			$siEntryInput = new SiEntryInput(SiIdentifier::parse($dataSet->reqArray('identifier')),
					$dataSet->reqString('buildupId'));
			foreach ($dataSet->reqArray('fieldInputMap', 'array') as $fieldId => $fielData) {
				$siEntryInput->putFieldInput($fieldId, new SiFieldInput($fielData));
			}
			return $siEntryInput;
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new CorruptedSiInputDataException(null, 0, $e);
		}
	}
}


class SiFieldInput {
	private $data;
	
	/**
	 * @param array $data
	 */
	function __construct(array $data) {
		$this->data = $data;
	}
	
	/**
	 * @return array
	 */
	function getData() {
		return $this->data;
	}
}


class SiError implements \JsonSerializable {
	private $entryErrors;
	
	/**
	 * @param SiEntryError[] $entryErrors
	 */
	function __construct(array $entryErrors) {
		$this->entryErrors = $entryErrors;
	}
	
	function jsonSerialize() {
		return [
				'entryErrors' => $this->entryErrors
		];
	}
}

class SiEntryError implements \JsonSerializable {
	// 	/**
	// 	 * @var string[]
	// 	 */
	// 	private $messages = [];
	/**
	 * @var SiFieldError[]
	 */
	private $fieldErrors = [];
	
	// 	function __construct(array $messages = []) {
	// 		ArgUtils::valArray($messages, 'string');
	// 		$this->messages = $messages;
	// 	}
	
	/**
	 * @param string $key
	 * @param SiFieldError $fieldError
	 */
	function putFieldError(string $key, SiFieldError $fieldError) {
		$this->fieldErrors[$key] = $fieldError;
	}
	
	function isEmpty() {
		return empty($this->fieldErrors);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
				'fieldErrors' => $this->fieldErrors
		];
	}
}

class SiFieldError implements \JsonSerializable {
	/**
	 * @var string[]
	 */
	private $messages = [];
	/**
	 * @var SiEntryError[]
	 */
	private $subEntryErrors = [];
	
	/**
	 *
	 */
	function __construct() {
	}
	
	/**
	 * @param string $message
	 */
	function addMessage(string $message) {
		$this->messages[] = $message;
	}
	
	/**
	 * @param string $key
	 * @param SiEntryError $entryError
	 */
	function putSubEntryError(string $key, SiEntryError $entryError) {
		$this->subEntryErrors[$key] = $entryError;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
				'messages' => $this->messages,
				'subEntryErrors' => $this->subEntryErrors
		];
	}
}