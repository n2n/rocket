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

use n2n\l10n\Message;
use n2n\persistence\meta\structure\UnknownIndexException;

class SiInput {
	/**
	 * @var SiEntryInput[] $entryInputs
	 */
	protected $entryInputs = [];
	
	/**
	 * @return SiEntryInput[];
	 */
	function getEntryInputs() {
		return $this->entryInputs;
	}
	
	/**
	 * @param string $key
	 * @param SiEntryInput $entryInput
	 */
	function putEntryInput(string $key, SiEntryInput $entryInput) {
		$this->entryInputs[$key] = $entryInput;
	}
}

class SiEntryInput {
	/**
	 * @var string
	 */
	private $category;
	/**
	 * @var string
	 */
	private $buildupId;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var SiFieldInput[]
	 */
	private $fieldInputs = [];
	
	/**
	 * @param string $category
	 * @param string $buildupId
	 * @param string $id
	 */
	function __construct(string $category, string $buildupId, string $id) {
		$this->category = $category;
		$this->buildupId = $buildupId;
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	function getCategory() {
		return $this->category;
	}
	
	/**
	 * @return string
	 */
	function getBuildupId() {
		return $this->buildupId;
	}
	
	/**
	 * @return string
	 */
	function getId() {
		return $this->id;
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
		
		throw new UnknownIndexException('Unknown field name: ' . $fieldName);
	}
	
	/**
	 * @return SiFieldInput[]
	 */
	function getFieldInputs() {
		return $this->fieldInputs;
	}
}


class SiFieldInput {
	private $data;
	
	/**
	 * @param array $data
	 * @param Message[] $errors
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