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
use n2n\util\type\TypeConstraints;

class SiEntryDeclaration implements \JsonSerializable {
	private $fieldDeclarationsMap = [];
	private $fieldStructureDeclarationsMap = [];
	
	// /**
	//  * @param SiFieldDeclaration[] $fieldDeclarations
	//  */
	// function __construct(array $fieldDeclarations = []) {
	// 	$this->setFieldDeclarations($fieldDeclarations);
	// }
	
	/**
	 * @param SiFieldDeclaration[] $siFieldDeclarations
	 * @return \rocket\si\structure\SiEntryDeclaration
	 */
	function setFieldDeclarationsMap(array $fieldDeclarations) {
		ArgUtils::valArray($fieldDeclarations,
				TypeConstraints::array(false, SiFieldDeclaration::class));
		$this->fieldDeclarations = $fieldDeclarations;
		return $this;
	}
	
	/**
	 * @param string $buildupId
	 * @param SiFieldDeclaration[] $fieldDeclarations
	 * @return SiEntryDeclaration
	 */
	function putFieldDeclarations(string $buildupId, array $fieldDeclarations) {
		ArgUtils::valArray($fieldDeclarations, SiFieldDeclaration::class);
		$this->fieldDeclarationsMap[$buildupId] = $fieldDeclarations;
		return $this;
	}
	
	/**
	 * @return array
	 */
	function getFieldDeclarationsMap() {
		return $this->fieldDeclarations;
	}

	
	/**
	 * @param SiFieldStructureDeclaration[] $fieldStructureDeclarations
	 * @return \rocket\si\structure\SiEntryDeclaration
	 */
	function setFieldStructureDeclarationsMap(array $fieldStructureDeclarations) {
		ArgUtils::valArray($fieldStructureDeclarations, 
				TypeConstraints::array(false, SiFieldStructureDeclaration::class));
		$this->fieldStructureDeclarationsMap = $fieldStructureDeclarations;
		return $this;
	}
	
	/**
	 * @param string $buildupId
	 * @param SiFieldStructureDeclaration[] $fieldStructureDeclarations
	 * @return \rocket\si\structure\SiEntryDeclaration
	 */
	function putFieldStructureDeclarations(string $buildupId, array $fieldStructureDeclarations) {
		ArgUtils::valArray($fieldStructureDeclarations, SiFieldStructureDeclaration::class);
		$this->fieldStructureDeclarationsMap[$buildupId] = $fieldStructureDeclarations;
		return $this;
	}
	
	/**
	 * @return array
	 */
	function getFieldStructureDeclarationsMap() {
		return $this->fieldStructureDeclarationsMap;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'fieldDeclarationsMap' => $this->fieldDeclarationsMap,
			'fieldStructureDeclarationsMap' => $this->fieldStructureDeclarationsMap
		];
	}
}