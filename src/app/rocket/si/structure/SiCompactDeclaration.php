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
namespace rocket\si\structure;

use n2n\util\type\ArgUtils;
use rocket\si\content\SiEntry;

class SiCompactDeclaration implements \JsonSerializable {
	private $fieldDeclarations;
	private $count;
	private $entries;
	
	/**
	 * @param SiFieldDeclaration[] $fieldDeclarations
	 * @param int $count
	 * @param SiEntry[] $entries
	 */
	function __construct(array $fieldDeclarations, int $count, array $entries = []) {
		$this->seFieldDeclarations($fieldDeclarations);
		$this->count = $count;
		$this->setEntries($entries);
	}
	
	/**
	 * @param SiFieldDeclaration[] $siFieldDeclarations
	 * @return \rocket\si\structure\SiCompactDeclaration
	 */
	function seFieldDeclarations(array $fieldDeclarations) {
		ArgUtils::valArray($fieldDeclarations, SiFieldDeclaration::class);
		$this->fieldDeclarations = $fieldDeclarations;
		return $this;
	}
	
	/**
	 * @return SiFieldDeclaration[]
	 */
	function getFieldDeclarations() {
		return $this->fieldDeclarations;
	}
	
	/**
	 * @param SiEntry[] $siEntries
	 * @return \rocket\si\structure\SiCompactDeclaration
	 */
	function setEntries(array $entries) {
		ArgUtils::valArray($entries, SiEntry::class);
		$this->entries = $entries;
		return $this;
	}
	
	/**
	 * @return SiEntry[]
	 */
	function getEntries() {
		return $this->entries;
	}
	
	function getCount() {
		return $this->count;
	}
	
	function setCount(int $count) {
		$this->count = $count;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'fieldDeclarations' => $this->fieldDeclarations,
			'entries' => $this->entries,
			'count' => $this->count
		];
	}
}