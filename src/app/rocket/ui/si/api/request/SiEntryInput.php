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
namespace rocket\ui\si\api\request;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\err\CorruptedSiDataException;

class SiEntryInput implements \JsonSerializable {

	/**
	 * @var SiFieldInput[] $fieldInputs
	 */
	private array $fieldInputs = [];

	/**
	 * @param string|null $entryId
	 */
	function __construct(private readonly string $maskId, private readonly ?string $entryId) {
	}

	function getMaskId(): string {
		return $this->maskId;
	}

	/**
	 * @return string|null
	 */
	function getEntryId(): ?string {
		return $this->entryId;
	}
	
	function isNew(): bool {
		return $this->entryId === null;
	}

	/**
	 * @param string $fieldName
	 * @param SiFieldInput $fieldInput
	 */
	function putFieldInput(string $fieldName, SiFieldInput $fieldInput): void {
		$this->fieldInputs[$fieldName] = $fieldInput;
	}
	
	/**
	 * @param string $fieldName
	 * @return bool
	 */
	function containsFieldName(string $fieldName): bool {
		return isset($this->fieldInputs[$fieldName]);
	}
	
	function getFieldInput(string $fieldName) {
		if (isset($this->fieldInputs[$fieldName])) {
			return $this->fieldInputs[$fieldName];
		}
		
		throw new \OutOfBoundsException('Unknown field name: ' . $fieldName);
	}
	
	/**
	 * @return string[] 
	 */
	function getFieldIds(): array {
		return array_keys($this->fieldInputs);
	}
	
	/**
	 * @return SiFieldInput[]
	 */
	function getFieldInputs(): array {
		return $this->fieldInputs;
	}
	
	/**
	 * @param array $data
	 * @return SiEntryInput
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiEntryInput {
		$dataSet = new DataSet($data);
		try {
			$siEntryInput = new SiEntryInput($dataSet->reqString('maskId'), $dataSet->optString('entryId'));
			foreach ($dataSet->reqArray('fieldInputMap', 'array') as $propId => $fieldData) {
				$siEntryInput->putFieldInput($propId, SiFieldInput::parse($fieldData));
			}
			return $siEntryInput;
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException($e->getMessage(), 0, $e);
		}
	}

	public function jsonSerialize(): mixed {
		return [
			'maskId' => $this->maskId,
			'entryId' => $this->entryId,
			'fieldInputMap' => $this->fieldInputs
		];
	}
}


class SiFieldInput implements \JsonSerializable {
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
	function getData(): array {
		return $this->data;
	}

	function jsonSerialize(): mixed {
		return $this->data;
	}

//	/**
//	 * @throws CorruptedSiDataException
//	 */
	static function parse(array $data): SiFieldInput {
//		try {
			return new SiFieldInput($data);
//		} catch (AttributesException $e) {
//			throw new CorruptedSiDataException($e->getMessage(), 0, $e);
//		}
	}
}

//class SiInputError implements \JsonSerializable {
//
//	/**
//	 * @param SiValueBoundary[] $valueBoundaries
//	 */
//	function __construct(private readonly array $valueBoundaries) {
//		ArgUtils::valArray($valueBoundaries, SiValueBoundary::class);
//	}
//
//	function getValueBoundaries(): array {
//		return $this->valueBoundaries;
//	}
//
//	function jsonSerialize(): mixed {
//		return [
//			'siValueBoundary' => $this->valueBoundaries
//		];
//	}
//}


// class SiEntryError implements \JsonSerializable {
// 	// 	/**
// 	// 	 * @var string[]
// 	// 	 */
// 	// 	private $messages = [];
// 	/**
// 	 * @var SiFieldError[]
// 	 */
// 	private $fieldErrors = [];
	
// 	// 	function __construct(array $messages = []) {
// 	// 		ArgUtils::valArray($messages, 'string');
// 	// 		$this->messages = $messages;
// 	// 	}
	
// 	/**
// 	 * @param string $key
// 	 * @param SiFieldError $fieldError
// 	 */
// 	function putFieldError(string $key, SiFieldError $fieldError) {
// 		$this->fieldErrors[$key] = $fieldError;
// 	}
	
// 	function isEmpty() {
// 		return empty($this->fieldErrors);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \JsonSerializable::jsonSerialize()
// 	 */
// 	function jsonSerialize(): mixed {
// 		return [
// 				'fieldErrors' => $this->fieldErrors
// 		];
// 	}
// }

// class SiFieldError implements \JsonSerializable {
// 	/**
// 	 * @var string[]
// 	 */
// 	private $messages = [];
// 	/**
// 	 * @var SiEntryError[]
// 	 */
// 	private $subEntryErrors = [];
	
// 	/**
// 	 *
// 	 */
// 	function __construct() {
// 	}
	
// 	/**
// 	 * @param string $message
// 	 */
// 	function addMessage(string $message) {
// 		$this->messages[] = $message;
// 	}
	
// 	/**
// 	 * @param string $key
// 	 * @param SiEntryError $entryError
// 	 */
// 	function putSubEntryError(string $key, SiEntryError $entryError) {
// 		$this->subEntryErrors[$key] = $entryError;
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \JsonSerializable::jsonSerialize()
// 	 */
// 	function jsonSerialize(): mixed {
// 		return [
// 				'messages' => $this->messages,
// 				'subEntryErrors' => $this->subEntryErrors
// 		];
// 	}
// }
