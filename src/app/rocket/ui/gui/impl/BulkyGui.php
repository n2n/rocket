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

namespace rocket\ui\gui\impl;

use rocket\ui\gui\Gui;
use rocket\ui\si\content\SiGui;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\content\impl\basic\BulkyEntrySiGui;
use rocket\ui\gui\GuiValueBoundary;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\meta\SiFrame;
use SiCallResult;
use rocket\ui\si\input\SiInputError;

class BulkyGui implements Gui {

	private SiGui $siGui;
	/**
	 * @var SiValueBoundary[]
	 */
	private array $inputSiValueBoundaries = [];
	private array $inputEiEntries = [];

	function __construct(private ?SiFrame $siFrame, private readonly SiDeclaration $siDeclaration,
			private readonly GuiValueBoundary $guiValueBoundary, private readonly bool $entrySiControlsIncluded = true) {

		$this->siGui = new BulkyEntrySiGui($this->siFrame, $this->siDeclaration,
				$this->guiValueBoundary->getSiValueBoundary());
		$this->siGui->setEntryControlsIncluded($this->entrySiControlsIncluded);
//		$siControls = $this->generalGuiControlMap?->getGuiControls() ?? [];
//		$this->siGui->setControls($siControls);
	}

	function getInputSiValueBoundaries(): array {
		return $this->inputSiValueBoundaries;
	}

	function getGuiValueBoundary(): GuiValueBoundary {
		return $this->guiValueBoundary;
	}

//	function handleSiGuiOperation(?SiInput $siInput, SiZoneCall $siGuiCall): SiCallResult {
//		$siInput->getValueBoundaryInputs();
//		$this->guiValueBoundary->handleSiEntryInput();
//	}

//	function handleSiInput(SiInput $siInput, N2nContext $n2nContext): ?SiInputError {
//		$entryInputs = $siInput->getValueBoundaryInputs();
//		if (count($entryInputs) > 1) {
//			throw new CorruptedSiDataException('BulkyEiGui can not handle multiple SiEntryInputs.');
//		}
//
//		$this->inputSiValueBoundaries = [];
//		$this->inputEiEntries = [];
//
//		foreach ($entryInputs as $siEntryInput) {
//			if (!$this->guiValueBoundary->getSiValueBoundary()->handleEntryInput($siEntryInput)
//					|| !$this->guiValueBoundary->save($n2nContext)) {
//				return new SiInputError([$this->guiValueBoundary->getSiValueBoundary()]);
//			}
//
//			if ($this->guiValueBoundary->getSelectedEiEntry()->validate()) {
//				$this->inputSiValueBoundaries = [$this->guiValueBoundary->getSiValueBoundary()];
//				$this->inputEiEntries = [$this->guiValueBoundary->getSelectedEiEntry()];
//				return null;
//			}
//
//			return new SiInputError([$this->guiValueBoundary->getSiValueBoundary($n2nLocale)]);
//		}
//
//		throw new IllegalStateException();
//	}

//	/**
//	 * @throws CorruptedSiDataException
//	 */
//	function handleSiCall(ZoneApiControlCallId $zoneControlCallId): SiCallResponse {
//		return $this->zoneGuiControlsMap->handleSiCall($zoneControlCallId, $this->eiFrame, $this->eiGuiDeclaration,
//				$this->inputEiEntries);
//	}


	function getSiGui(): SiGui {
		return $this->siGui;
	}

}