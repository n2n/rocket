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

use n2n\util\uri\Url;
use rocket\si\content\SiEntry;

class SiSplitContent implements \JsonSerializable {
	private $label;
	
	private $apiUrl;
	private $entryId;
	private $bulky;
	/**
	 * @var SiEntry
	 */
	private $entry;
	
	private function __construct() {
	}
	
	function jsonSerialize() {
		$data = [ 'label' => $this->label ];
		
		if ($this->apiUrl !== null) {
			$data['apiUrl'] = $this->apiUrl;
			$data['entryId'] = $this->entryId;
			$data['bulky'] = $this->bulky;
		}
		
		if ($this->entry !== null) {
			$data['entry'] = $this->entry;
		}
		
		return $data;
	}
	
	static function createUnavaialble(string $label) {
		$split = new SiSplitContent();
		$split->label = $label;
		return $split;
	}
	
	/**
	 * @param string $label
	 * @param Url $apiUrl
	 * @param string $entryId
	 * @param bool $bulky
	 * @return \rocket\si\content\impl\split\SiSplitContent
	 */
	static function createLazy(string $label, Url $apiUrl, string $entryId, bool $bulky) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->apiUrl = $apiUrl;
		$split->entryId = $entryId;
		$split->bulky = $bulky;
		return $split;
	}
	
	/**
	 * @param string $label
	 * @param SiEntry $entry
	 * @return \rocket\si\content\impl\split\SiSplitContent
	 */
	static function createEntry(string $label, SiEntry $entry) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->entry = $entry;
		return $split;
	}
}
