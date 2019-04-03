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
namespace rocket\gi\content;

use n2n\util\type\ArgUtils;

class GiBulkyContent {
	private $giFieldStructureDeclarations;
	private $giEntries;
	
	function __construct(array $giFieldStructureDeclarations, array $giEntries = []) {
		$this->setGiFieldStructureDeclarations($giFieldStructureDeclarations);
		$this->setGiEntries($giEntries);
	}
	
	/**
	 * @param GiFieldStructureDeclaration[] $giFieldStructureDeclarations
	 * @return \rocket\gi\content\GiCompactContent
	 */
	function setGiFieldStructureDeclarations(array $giFieldStructureDeclarations) {
		ArgUtils::valArray($giFieldStructureDeclarations, GiFieldStructureDeclaration::class);
		$this->giFieldStructureDeclarations = $giFieldStructureDeclarations;
		return $this;
	}
	
	/**
	 * @return GiFieldStructureDeclaration[]
	 */
	function getGiFieldStructureDeclarations() {
		return $this->giFieldStructureDeclarations;
	}
	
	/**
	 * @param GiEntry[] $giEntries
	 * @return \rocket\gi\content\GiCompactContent
	 */
	function setGiEntries(array $giEntries) {
		ArgUtils::valArray($giEntries, GiEntry::class);
		$this->giEntries = $giEntries;
		return $this;
	}
	
	/**
	 * @return GiEntry[]
	 */
	function getGiEntries() {
		return $this->giEntries;
	}
}