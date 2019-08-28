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
namespace rocket\si\api;

use rocket\si\structure\SiEntryDeclaration;
use rocket\si\content\SiEntry;
use rocket\si\content\SiPartialContent;

class SiGetResult implements \JsonSerializable {
	/**
	 * @var SiEntryDeclaration|null
	 */
	private $entryDeclaration = null;
	/**
	 * @var SiEntry|null
	 */
	private $entry = null;
	
	/**
	 *
	 */
	function __construct() {
	}
	
	/**
	 * @return \rocket\si\structure\SiEntryDeclaration|null
	 */
	public function getEntryDeclaration() {
		return $this->entryDeclaration;
	}
	
	/**
	 * @param \rocket\si\structure\SiEntryDeclaration|null $entryDeclaration
	 */
	public function setEntryDeclaration(?SiEntryDeclaration $entryDeclaration) {
		$this->entryDeclaration = $entryDeclaration;
	}
	
	/**
	 * @return \rocket\si\content\SiEntry
	 */
	public function getEntry() {
		return $this->entry;
	}
	
	/**
	 * @param \rocket\si\content\SiEntry|null $entries
	 */
	public function setEntry(?SiEntry $entry) {
		$this->entry = $entry;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	public function jsonSerialize() {
		return [
			'entryDeclaration' => $this->entryDeclaration,
			'entry' => $this->entry
		];
	}
}
