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
namespace rocket\si\content\impl\split;

use rocket\si\SiPayloadFactory;
use n2n\util\uri\Url;
use rocket\si\content\SiField;
use n2n\util\ex\IllegalStateException;
use n2n\web\http\UploadDefinition;

class SiSplitContent implements \JsonSerializable {
	private $label;
	
	private $apiUrl;
	private $entryId;
	private $fieldId;
	private $bulky;
	/**
	 * @var \Closure|null
	 */
	private $handleInputCallback;
	/**
	 * @var SiField|null
	 */
	private $field;
	
	private function __construct() {
	}
	
	function jsonSerialize() {
		$data = [ 'label' => $this->label ];
		
		if ($this->apiUrl !== null) {
			$data['apiUrl'] = $this->apiUrl;
			$data['entryId'] = $this->entryId;
			$data['fieldId'] = $this->fieldId;
			$data['bulky'] = $this->bulky;
			$data['readOnly'] = $this->handleInputCallback === null;
		}
		
		if ($this->field !== null) {
			$data['field'] = SiPayloadFactory::createDataFromField($this->field);
		}
		
		return $data;
	}
	
	function isReadOnly() {
		if ($this->apiUrl !== null) {
			return $this->handleInputCallback === null;
		}
		
		if ($this->field !== null) {
			return $this->field->isReadOnly();
		}
		
		return false;
	}
	
	/**
	 * @param array $data
	 * @param UploadDefinition[] $uploadDefinitions
	 * @return array
	 */
	function handleInput(array $data, array $uploadDefinitions) {
		if ($this->handleInputCallback !== null) {
			return $this->handleInputCallback($data);
		}
		
		if ($this->field !== null) {
			$this->field->handleCall($data, $uploadDefinitions);
		}
	}
	
	static function createUnavaialble(string $label) {
		$split = new SiSplitContent();
		$split->label = $label;
		return $split;
	}
	
	static function createLazy(string $label, Url $apiUrl, string $entryId, string $fieldId, bool $bulky,
			\Closure $handleInputCallback = null) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->apiUrl = $apiUrl;
		$split->entryId = $entryId;
		$split->fieldId = $fieldId;
		$split->bulky = $bulky;
		$split->handleInputCallback = $handleInputCallback;
		return $split;
	}
	
	static function createField(string $label, SiField $field) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->field = $field;
		return $split;
	}
}
