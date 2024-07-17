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
use rocket\ui\gui\control\GuiControl;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\control\SiIconType;
use rocket\ui\si\content\SiEntry;
use rocket\ui\si\api\request\SiEntryInput;
use n2n\core\container\N2nContext;
use rocket\ui\si\err\CorruptedSiDataException;

class CuMaskedEntry {


	private SiMask $siMaskDeclaration;
	private CuStructure $eifSiStructure;
	private CuGuiEntry $cuGuiEntry;


	/**
	 * @var array<GuiControl>
	 */
	private array $guiControls = [];

	function __construct(private string $maskId, string $typeId, string $name,
			$iconClass = SiIconType::ICON_ROCKET) {

		$this->siMaskDeclaration = new SiMask(
				new SiMask(new SiMaskQualifier(new SiMaskIdentifier($maskId, $typeId), $name, $iconClass)),
				[]);
		$this->cuGuiEntry = new CuGuiEntry();

		$this->eifSiStructure = new CuStructure($this->cuGuiEntry, $this->siMaskDeclaration, null);
	}

	function getMaskId(): string {
		return $this->maskId;
	}

	function structure(): CuStructure {
		return $this->eifSiStructure;
	}

	function getSiMask(): SiMask {
		return $this->siMaskDeclaration;
	}

	function getCuEntry(): CuGuiEntry {
		return $this->cuGuiEntry;
	}

	function getSiEntry(): SiEntry {
		return $this->cuGuiEntry->getSiEntry();
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput, N2nContext $n2nContext): bool {
		return $this->cuGuiEntry->handleSiEntryInput($siEntryInput, $n2nContext);
	}
}