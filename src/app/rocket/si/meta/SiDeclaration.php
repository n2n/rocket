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
namespace rocket\si\meta;

use n2n\util\type\ArgUtils;

class SiDeclaration implements \JsonSerializable {
	/**
	 * @var \rocket\si\meta\SiTypeDeclaration[]
	 */
	private $typeDeclarations = [];
	
	/**
	 * @param SiTypeDeclaration[] $typedDeclarations
	 */
	function __construct(array $typedDeclarations = []) {
		$this->setTypeDeclarations($typedDeclarations);
	}
	
	/**
	 * @param SiTypeDeclaration[] $typedDeclarations
	 * @return \rocket\si\meta\SiDeclaration
	 */
	function setTypeDeclarations(array $typedDeclarations) {
		ArgUtils::valArray($typedDeclarations, SiTypeDeclaration::class);
		$this->siTypeDeclarations = $typedDeclarations;
		return $this;
	}
	
	/**
	 * @param string $typeId
	 * @param SiTypeDeclaration $typeDeclaration
	 * @return SiDeclaration
	 */
	function addTypeDeclaration(SiTypeDeclaration $typeDeclaration) {
		$this->siTypeDeclarations[] = $typeDeclaration;
		return $this;
	}
	
	/**
	 * @return SiTypeDeclaration[]
	 */
	function getTypeDeclarations() {
		return $this->siTypeDeclarations;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'typeDeclarations' => $this->siTypeDeclarations
		];
	}
}