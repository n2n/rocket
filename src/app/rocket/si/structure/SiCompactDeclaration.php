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
use n2n\util\type\TypeConstraints;

class SiCompactDeclaration implements \JsonSerializable {
	private $fieldDeclarations;
	
	/**
	 * @param SiFieldDeclaration[] $fieldDeclarations
	 * @param int $count
	 * @param SiEntry[] $entries
	 */
	function __construct(array $fieldDeclarations = []) {
		$this->seFieldDeclarations($fieldDeclarations);
	}
	
	/**
	 * @param SiFieldDeclaration[] $siFieldDeclarations
	 * @return \rocket\si\structure\SiCompactDeclaration
	 */
	function seFieldDeclarations(array $fieldDeclarations) {
		ArgUtils::valArray($fieldDeclarations,
				TypeConstraints::array(false, SiFieldDeclaration::class));
		$this->fieldDeclarations = $fieldDeclarations;
		return $this;
	}
	
	/**
	 * @param string $buildupId
	 * @param SiFieldDeclaration[] $fieldDeclarations
	 */
	function putFieldDeclarations(string $buildupId, array $fieldDeclarations) {
		ArgUtils::valArray($fieldDeclarations, SiFieldDeclaration::class);
		$this->fieldDeclarations[$buildupId] = $fieldDeclarations;
	}
	
	/**
	 * @return SiFieldDeclaration[]
	 */
	function getFieldDeclarations() {
		return $this->fieldDeclarations;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'fieldDeclarations' => $this->fieldDeclarations
		];
	}
}