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

namespace rocket\op\cu\util\gui;

use rocket\op\cu\gui\CuMaskedEntry;
use rocket\op\cu\gui\field\CuField;
use rocket\ui\si\meta\SiStructureType;
use rocket\impl\cu\gui\BulkyCuGui;
use rocket\op\cu\gui\CuGui;
use rocket\op\cu\gui\control\CuControl;

class CufBulkyGui implements CufGui {

	private CuMaskedEntry $cuMaskedEntry;
	private BulkyCuGui $bulkyCuGui;

	function __construct(bool $readOnly) {
		$maskId = 'mask-cuf-bulky-gui';
		$typeId = 'type-cuf-bulky-gui';

		$this->cuMaskedEntry = new CuMaskedEntry($maskId, $typeId, 'Unnamed Boundary');
		$this->bulkyCuGui = new BulkyCuGui($readOnly);
		$this->bulkyCuGui->addCuMaskedEntry($this->cuMaskedEntry);
		$this->bulkyCuGui->setSelectedMaskId($maskId);
	}

	function addField(string $propId, string $label, CuField $cuField, ?string $helpText = null,
			string $siStructureType = SiStructureType::ITEM): static {
		$this->cuMaskedEntry->structure()->addCuField($propId, $label, $cuField, $helpText, $siStructureType);
		return $this;
	}

	function addControl(CuControl $cuControl): static {
		$this->bulkyCuGui->addCuControl($cuControl);
		return $this;
	}

	function getCuGui(): CuGui {
		return $this->bulkyCuGui;
	}

}
