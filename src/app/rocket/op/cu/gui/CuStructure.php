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

namespace rocket\op\cu\gui;

use rocket\ui\si\meta\SiMask;
use rocket\ui\si\meta\SiStructureDeclaration;
use rocket\ui\si\meta\SiProp;
use rocket\ui\si\meta\SiStructureType;
use n2n\util\ex\DuplicateElementException;
use rocket\op\cu\gui\field\CuField;

class CuStructure {

	function __construct(private readonly CuGuiEntry $cuGuiEntry,
			private readonly SiMask $siMaskDeclaration,
			private readonly ?SiStructureDeclaration $siStructureDeclaration) {
	}

	function addCuField(string $propId, string $label, CuField $cuField, string $helpText = null,
			string $siStructureType = SiStructureType::ITEM): static {
		if ($this->cuGuiEntry->containsCuField($propId)) {
			throw new DuplicateElementException('Property id already exist: ' . $propId);
		}

		$siProp = new SiProp($propId, $label);
		$siProp->setHelpText($helpText);
		$this->siMaskDeclaration->getMask()->addProp($siProp);

		$this->addSiStructureDeclaration(SiStructureDeclaration::createProp($siStructureType, $propId));

		$this->cuGuiEntry->putCuField($propId, $cuField);

		return $this;
	}

	private function addSiStructureDeclaration(SiStructureDeclaration $siStructureDeclaration): void {
		if ($this->siStructureDeclaration !== null) {
			$this->siStructureDeclaration->addChild($siStructureDeclaration);
			return;
		}

		$this->siMaskDeclaration->addStructureDeclaration($siStructureDeclaration);
	}

}