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
use rocket\ui\gui\impl\BulkyGui;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\meta\SiMask;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\control\SiIconType;
use rocket\ui\gui\GuiValueBoundary;
use rocket\ui\gui\GuiEntry;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\ui\si\content\SiEntryIdentifier;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiField;
use rocket\ui\gui\field\GuiPropKey;
use rocket\ui\si\meta\SiProp;
use rocket\ui\si\meta\SiStructureDeclaration;
use rocket\ui\gui\Gui;
use rocket\ui\si\content\SiGui;

class CufBulkyGui implements Gui {

	private BulkyGui $bulkyGui;

	private SiMask $siMask;
	private GuiEntry $guiEntry;
	private GuiFieldMap $guiFieldMap;

	function __construct(bool $readOnly) {
		$maskId = 'mask-cuf-bulky-gui';
		$typeId = 'type-cuf-bulky-gui';

		$siMaskIdentifier = new SiMaskIdentifier($maskId, $typeId);
		$this->siMask = new SiMask(new SiMaskQualifier($siMaskIdentifier, 'Custom Mask',
				SiIconType::ICON_ROCKET));

		$guiValueBoundary = new GuiValueBoundary();
		$this->guiEntry = new GuiEntry(new SiEntryQualifier(new SiEntryIdentifier($siMaskIdentifier, null)));
		$guiValueBoundary->putGuiEntry($this->guiEntry);
		$this->guiFieldMap = new GuiFieldMap();
		$guiValueBoundary->selectGuiEntryByMaskId($maskId);
		$this->bulkyGui = new BulkyGui(null, new SiDeclaration([$this->siMask]), $guiValueBoundary);
	}

	function addField(string $propId, string $label, GuiField $guiField, ?string $helpText = null,
			string $siStructureType = SiStructureType::ITEM): static {

		$this->guiFieldMap->putGuiField(new GuiPropKey($propId), $guiField);
		$this->siMask->putProp($propId, (new SiProp($label))->setHelpText($helpText));
		$this->siMask->addStructureDeclaration(SiStructureDeclaration::createProp($siStructureType, $propId));
		return $this;
	}

	function getSiGui(): SiGui {
		if (!$this->guiEntry->isInitialized()) {
			$this->guiEntry->init($this->guiFieldMap, null);
		}

		return $this->bulkyGui->getSiGui();
	}

	function getValue(string $propId): mixed {
		return $this->guiFieldMap->getGuiField($propId)->getValue();
	}

}
