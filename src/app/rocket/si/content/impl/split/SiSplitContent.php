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

class SiSplitContent implements \JsonSerializable {
	private $label;
	
	private $apiUrl;
	private $entryId;
	private $fieldId;
	private $bulky;
	
	private $field;
	
	private function __construct() {
		
	}
	
	function jsonSerialize() {
		$data = [
			'label' => $this->label,
			'apiUrl' => $this->apiUrl,
			'entryId' => $this->entryId,
			'fieldId' => $this->fieldId,
			'bulky' => $this->bulky
		];
		
		if ($this->field !== null) {
			$this->field = SiPayloadFactory::createDataFromField($this->field);
		}
		
		return $data;
	}
	
	static function createUnavaialble(string $label) {
		$split = new SiSplitContent();
		$split->label = $label;
		return $split;
	}
	
	static function createLazy(string $label, Url $apiUrl, string $entryId, string $fieldId, bool $bulky) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->apiUrl = $apiUrl;
		$split->entryId = $entryId;
		$split->fieldId = $fieldId;
		$split->bulky = $bulky;
		return $split;
	}
	
	static function createField(string $label, SiField $field) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->field = $field;
		return $split;
	}
}
