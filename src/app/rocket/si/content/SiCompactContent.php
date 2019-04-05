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
namespace rocket\si\content;

use n2n\util\type\ArgUtils;

class SiCompactContent implements \JsonSerializable {
	private $siFieldDeclarations;
	private $siEntries;
	
	function __construct(array $siFieldDeclarations, array $siEntries = []) {
		$this->setSiFieldDeclarations($siFieldDeclarations);
		$this->setSiEntries($siEntries);
	}
	
	/**
	 * @param SiFieldDeclaration[] $siFieldDeclarations
	 * @return \rocket\si\content\SiCompactContent
	 */
	function setSiFieldDeclarations(array $siFieldDeclarations) {
		ArgUtils::valArray($siFieldDeclarations, SiFieldDeclaration::class);
		$this->siFieldDeclarations = $siFieldDeclarations;
		return $this;
	}
	
	/**
	 * @return SiFieldDeclaration[]
	 */
	function getSiFieldDeclarations() {
		return $this->siFieldDeclarations;
	}
	
	/**
	 * @param SiEntry[] $siEntries
	 * @return \rocket\si\content\SiCompactContent
	 */
	function setSiEntries(array $siEntries) {
		ArgUtils::valArray($siEntries, SiEntry::class);
		$this->siEntries = $siEntries;
		return $this;
	}
	
	/**
	 * @return SiEntry[]
	 */
	function getSiEntries() {
		return $this->siEntries;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'siFieldDeclarations' => $this->siFieldDeclarations,
			'siEntries' => $this->siEntries
		];
	}
}