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
use rocket\ui\si\api\request\SiInput;
use n2n\util\ex\NotYetImplementedException;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\si\content\impl\basic\CompactExplorerSiGui;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\meta\SiDeclaration;
use rocket\impl\ei\manage\gui\SiInputError;

class CompactExplorerGui implements Gui {

	private SiGui $siGui;

	function __construct(private SiFrame $siFrame, private int $pageSize, private SiDeclaration $siDeclaration,
			private ?SiPartialContent $partialContent) {

		$this->siGui = new CompactExplorerSiGui($this->siFrame, $pageSize, $this->siDeclaration,
				$this->partialContent);

	}

	function handleSiInput(SiInput $siInput): ?SiInputError {
		throw new NotYetImplementedException();

//		$eiFrameUtil = new EiFrameUtil($this->eiFrame);
//
//		foreach ($siInput->getEntryInputs() as $entryInput) {
//			if (null !== ($pid = $entryInput->getIdentifier()->getId())) {
//				$eiObject = $eiFrameUtil->lookupEiObject($eiFrameUtil->pidToId($pid));
//			} else {
//				$eiObject = $eiFrameUtil->createNewEiObject($entryInput->getMaskId());
//			}
//
//			$eiEntry = $this->eiFrame->createEiEntry($eiObject);
//			$inputEiGuiDeclaration = $eiGuiDeclaration = $eiFrameUtil->createEiGuiDeclaration($eiEntry->getEiMask(), $this->eiGuiDeclaration->getViewMode());
////$eiGuiValueBoundary = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame, [$eiEntry], $this->eiGui);
////$eiGuiValueBoundary->handleSiEntryInput($entryInput);
//			//
//
////}
////
////$eiGuiValueBoundary->save();
////
////$this->inputEiEntries[$key] = $eiEntry;
////$this->inputEiGuiDeclarations[$key] = $inputEiGuiDeclaration;
////
////if ($eiEntry->validate()) {
////	continue;
////}
////
////$errorEntries[$key] = $eiGuiDeclaration->createSiEntry($this->eiFrameUtil->getEiFrame(), $eiGuiValueBoundary, false);
////}
//
//			$this->eiGuiValueBoundary->handleSiEntryInput($siEntryInput);
//
//			$this->eiGuiValueBoundary->save();
//
//			if ($this->eiGuiValueBoundary->getSelectedEiEntry()->validate()) {
//				$this->inputSiValueBoundaries = [$this->eiGuiValueBoundary->createSiValueBoundary()];
//				return null;
//			}
//
//			return new SiInputError([$this->eiGuiValueBoundary->createSiValueBoundary()]);
//		}
//
//		$eiObject = null;

	}


	function getSiGui(): SiGui {
		return $this->siGui;
	}
}