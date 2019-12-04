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
use rocket\si\content\impl\OutSiFieldAdapter;

class SplitContextOutSiField extends OutSiFieldAdapter {

	/**
	 * @var SiSplitContent[]
	 */
	private $splitContents = [];
	
	/**
	 * 
	 */
	function __construct() {
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-context-out';
	}
		
	/**
	 * @param string $key
	 * @param string $label
	 * @param SiEntry $entry
	 * @return \rocket\si\content\impl\split\SplitContextOutSiField
	 */
	function putEntry(string $key, string $label, SiEntry $entry) {
		$this->splitContents[$key] = SiSplitContent::createEntry($label, $entry);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param Url $apiUrl
	 * @param string $entryId
	 * @param bool $bulky
	 * @return \rocket\si\content\impl\split\SplitContextOutSiField
	 */
	function putLazy(string $key, string $label, Url $apiUrl, string $entryId, bool $bulky) {
		$this->splitContents[$key] = SiSplitContent::createLazy($label, $apiUrl, $entryId, $bulky);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @return \rocket\si\content\impl\split\SplitContextOutSiField
	 */
	function putUnavailable(string $key, string $label) {
		$this->splitContents[$key] = SiSplitContent::createUnavaialble($label);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'splitContents' => $this->splitContents
		];
	}
}
