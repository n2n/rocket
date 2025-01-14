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
namespace rocket\ui\si\api\response;

use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\content\SiValueBoundary;
use n2n\core\container\N2nContext;

class SiValGetInstructionResult {
	/**
	 * @var SiDeclaration|null
	 */
	private $declaration = null;
	/**
	 * @var SiValueBoundary|null
	 */
	private $valueBoundary = null;

	function __construct() {
	}

	/**
	 * @return \rocket\ui\si\meta\SiDeclaration|null
	 */
	public function getDeclaration() {
		return $this->declaration;
	}

	/**
	 * @param \rocket\ui\si\meta\SiDeclaration|null $declaration
	 */
	public function setDeclaration(?SiDeclaration $declaration) {
		$this->declaration = $declaration;
	}

	/**
	 * @return \rocket\ui\si\content\SiValueBoundary
	 */
	public function getValueBoundary() {
		return $this->valueBoundary;
	}

	/**
	 * @param \rocket\ui\si\content\SiValueBoundary|null $entries
	 */
	public function setValueBoundary(?SiValueBoundary $entry) {
		$this->valueBoundary = $entry;
	}

	public function toJsonStruct(N2nContext $n2nContext): array {
		return [
			'declaration' => $this->declaration,
			'valueBoundary' => $this->valueBoundary->toJsonStruct($n2nContext)
		];
	}
}