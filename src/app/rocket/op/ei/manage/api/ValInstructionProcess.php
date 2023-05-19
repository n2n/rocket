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
namespace rocket\op\ei\manage\api;

use rocket\si\api\SiValResponse;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\si\api\SiValResult;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\op\ei\manage\gui\EiGuiValueBoundary;
use rocket\si\api\SiPartialContentInstruction;
use rocket\si\content\SiValueBoundary;
use rocket\si\api\SiValInstruction;
use rocket\si\input\SiEntryInput;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\si\api\SiValGetResult;
use rocket\si\api\SiValGetInstruction;
use rocket\op\ei\manage\frame\EiGuiValueBoundaryResult;
use rocket\op\ei\manage\gui\EiGui;

class ValInstructionProcess {
	private $instruction;
	private $util;
	private $apiUtil;
	private $eiFrameUtil;
	
	private $eiEntry = null;
	private $eiGuiValueBoundaryResults = null;
	
	function __construct(SiValInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ProcessUtil($eiFrame);
		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	function clear() {
		$this->eiEntry = null;
		$this->eiGuiValueBoundaryResults = [];
	}
	
	/**
	 * @return SiValResponse 
	 */
	function exec() {
		IllegalStateException::assertTrue($this->eiEntry === null);
		
		$entryInput = $this->instruction->getEntryInput();
		
		$eiGui = $this->util->determineEiGuiOfInput($entryInput);
		$this->eiEntry = $eiGui->getEiGuiValueBoundary()->getSelectedEiGuiEntry()->getEiEntry();

		$result = new SiValResult($this->util->handleEntryInput($entryInput, $eiGui->getEiGuiValueBoundary()));
// 		$result->setEntryError();
		
		foreach ($this->instruction->getGetInstructions() as $key => $getInstruction) {
			$result->putGetResult($key, $this->handleGetInstruction($getInstruction));
		}
		
		$this->clear();
		
		return $result;
	}
	
	/**
	 * @param SiValGetInstruction $getInstruction
	 * @return SiValGetResult
	 */
	private function handleGetInstruction($getInstruction) {
		$eiGui = $this->util->determineEiGuiOfEiEntry($this->eiEntry, $this->instruction->getEntryInput()->getMaskId(),
					$getInstruction->getStyle()->isBulky(), $getInstruction->getStyle()->isReadOnly());
		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$result = new SiValGetResult();
		$result->setEntry($eiGui->createSiEntry($eiFrame, $getInstruction->areControlsIncluded()));
		
		if ($getInstruction->isDeclarationRequested()) {
			$result->setDeclaration($eiGui->getEiGuiDeclaration()->createSiDeclaration($eiFrame));
		}
		
		return $result;
	}

	private function registerEiGui(EiGuiValueBoundaryResult $eiGui): void {
		$this->eiGuiValueBoundaryResults[$eiGui->getEiGuiDeclaration()->getViewMode()] = $eiGui;
	}
	
	/**
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return EiGuiValueBoundaryResult
	 */
	private function obtainEiGuiValueBoundaryResult(bool $bulky, bool $readOnly): EiGuiValueBoundaryResult {
		$viewMode = ViewMode::determine($bulky, $readOnly, $this->eiEntry->isNew());
		if (isset($this->eiGuiValueBoundaryResults[$viewMode])) {
			return $this->eiGuiValueBoundaryResults[$viewMode];
		}
		
		$eiGuiValueBoundaryResult = $this->eiFrameUtil->createEiGuiValueBoundary($this->eiEntry, $bulky, $readOnly, null, true);
		$this->registerEiGui($eiGuiValueBoundaryResult);
		return $eiGuiValueBoundaryResult;
	}
	
	/**
	 * @param string $entryId
	 * @return \rocket\si\api\SiValResult
	 */
	private function handleEntryInput(SiEntryInput $entryInput) {
		
	}
	
	/**
	 * @return \rocket\si\api\SiValResult
	 */
	private function handleNewEntry() {
		$eiGuiValueBoundaryMulti = $this->eiFrameUtil->createNewEiGuiValueBoundaryMulti(
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
				
		return $this->createEntryResult($eiGuiValueBoundaryMulti->createSiEntry(), $eiGuiValueBoundaryMulti->getEiGuiValueBoundaries());	
	}
	
	/**
	 * @param SiValueBoundary $siEntry
	 * @param EiGuiValueBoundary[] $eiGuiValueBoundaries
	 * @return \rocket\si\api\SiValResult
	 */
	private function createEntryResult(SiValueBoundary $siValueBoundary, array $eiGuiValueBoundaries) {
		$result = new SiValResult();
		$result->setEntry($siValueBoundary);
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiGuiValueBoundaries));
		} else {
			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiGuiValueBoundaries));
		}
		
		return $result;
	}
	
	private function handlePartialContent(SiPartialContentInstruction $spci) {
		$num = $this->eiFrameUtil->count();
		$eiGuiMaskDeclaration = $this->eiFrameUtil->lookupEiGuiMaskDeclarationFromRange($spci->getFrom(), $spci->getNum(),
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
		
		$result = new SiValResult();
		$result->setPartialContent($this->apiUtil->createSiPartialContent($spci->getFrom(), $num, $eiGuiMaskDeclaration));
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiMaskDeclaration));
		} else {
			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiMaskDeclaration));
		}
		
		return $result;
	}
}