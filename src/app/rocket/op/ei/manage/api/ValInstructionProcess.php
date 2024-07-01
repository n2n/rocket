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

use rocket\op\ei\manage\frame\EiFrame;
use rocket\si\api\SiValResult;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\ui\si\api\SiValInstruction;
use n2n\util\ex\IllegalStateException;
use SiValGetInstructionResult;
use rocket\ui\si\api\SiValGetInstruction;
use rocket\op\ei\manage\frame\EiGuiValueBoundaryResult;
use n2n\web\http\BadRequestException;
use rocket\op\ei\manage\entry\EiEntry;

class ValInstructionProcess {
	private $instruction;
	private $util;
//	private $apiUtil;
	private $eiFrameUtil;
	
	private ?EiEntry $eiEntry = null;
//	private $eiGuiValueBoundaryResults = null;
	
	function __construct(SiValInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ProcessUtil($eiFrame);
//		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiObjectSelector($eiFrame);
	}
	
	function clear() {
		$this->eiEntry = null;
//		$this->eiGuiValueBoundaryResults = [];
	}

	/**
	 * @return SiValResult
	 * @throws BadRequestException
	 */
	function exec(): SiValResult {
		IllegalStateException::assertTrue($this->eiEntry === null);
		
		$entryInput = $this->instruction->getValueBoundaryInput();
		
		$eiGuiValueBoundary = $this->util->determineEiGuiValueBoundaryOfInput($entryInput);
		$this->eiEntry = $eiGuiValueBoundary->getSelectedEiGuiEntry()->getEiEntry();

		$result = new SiValResult($this->util->handleEntryInput($entryInput, $eiGuiValueBoundary));
// 		$result->setEntryError();
		
		foreach ($this->instruction->getGetInstructions() as $key => $getInstruction) {
			$result->putGetResult($key, $this->handleGetInstruction($getInstruction));
		}
		
		$this->clear();
		
		return $result;
	}

	/**
	 * @param SiValGetInstruction $getInstruction
	 * @return SiValGetInstructionResult
	 * @throws BadRequestException
	 */
	private function handleGetInstruction(SiValGetInstruction $getInstruction): SiValGetInstructionResult {
		$eiGui = $this->util->determineEiGuiOfEiEntry($this->eiEntry, $this->instruction->getValueBoundaryInput()->getMaskId(),
					$getInstruction->getStyle()->isBulky(), $getInstruction->getStyle()->isReadOnly(),
				$getInstruction->areControlsIncluded());
		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$result = new SiValGetInstructionResult();
		$result->setValueBoundary($eiGui->createSiValueBoundary($eiFrame->getN2nContext()->getN2nLocale()));
		
		if ($getInstruction->isDeclarationRequested()) {
			$result->setDeclaration($eiGui->getEiGuiDeclaration()->createSiDeclaration($eiFrame->getN2nContext()->getN2nLocale()));
		}
		
		return $result;
	}

	private function registerEiGui(EiGuiValueBoundaryResult $eiGui): void {
		$this->eiGuiValueBoundaryResults[$eiGui->getEiGuiDeclaration()->getViewMode()] = $eiGui;
	}
	
//	/**
//	 * @param bool $bulky
//	 * @param bool $readOnly
//	 * @return EiGuiValueBoundaryResult
//	 */
//	private function obtainEiGuiValueBoundaryResult(bool $bulky, bool $readOnly): EiGuiValueBoundaryResult {
//		$viewMode = ViewMode::determine($bulky, $readOnly, $this->eiEntry->isNew());
//		if (isset($this->eiGuiValueBoundaryResults[$viewMode])) {
//			return $this->eiGuiValueBoundaryResults[$viewMode];
//		}
//
//		$eiGuiValueBoundaryResult = $this->eiFrameUtil->createEiGuiValueBoundary($this->eiEntry, $bulky, $readOnly, true, null, );
//		$this->registerEiGui($eiGuiValueBoundaryResult);
//		return $eiGuiValueBoundaryResult;
//	}
	
//	/**
//	 * @param string $entryId
//	 * @return \rocket\si\api\SiValResult
//	 */
//	private function handleEntryInput(SiEntryInput $entryInput) {
//
//	}
	
//	/**
//	 * @return \rocket\si\api\SiValResult
//	 */
//	private function handleNewEntry() {
//		$eiGuiValueBoundaryMulti = $this->eiFrameUtil->createNewEiGuiValueBoundary(
//				$this->instruction->isBulky(), $this->instruction->isReadOnly(), $this->instruction->);
//
//		return $this->createEntryResult($eiGuiValueBoundaryMulti->createSiEntry(), $eiGuiValueBoundaryMulti->getEiGuiValueBoundaries());
//	}
	
//	/**
//	 * @param SiValueBoundary $siEntry
//	 * @param EiGuiValueBoundary[] $eiGuiValueBoundaries
//	 * @return \rocket\si\api\SiValResult
//	 */
//	private function createEntryResult(SiValueBoundary $siValueBoundary, array $eiGuiValueBoundaries) {
//		$result = new SiValResult();
//		$result->setEntry($siValueBoundary);
//
//		if (!$this->instruction->isDeclarationRequested()) {
//			return $result;
//		}
//
//		if ($this->instruction->isBulky()) {
//			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiGuiValueBoundaries));
//		} else {
//			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiGuiValueBoundaries));
//		}
//
//		return $result;
//	}
	
//	private function handlePartialContent(SiPartialContentInstruction $spci) {
//		$num = $this->eiFrameUtil->count();
//		$eiGuiMaskDeclaration = $this->eiFrameUtil->lookupEiGuiMaskDeclarationFromRange($spci->getFrom(), $spci->getNum(),
//				$this->instruction->isBulky(), $this->instruction->isReadOnly());
//
//		$result = new SiValResult();
//		$result->setPartialContent($this->apiUtil->createSiPartialContent($spci->getFrom(), $num, $eiGuiMaskDeclaration));
//
//		if (!$this->instruction->isDeclarationRequested()) {
//			return $result;
//		}
//
//		if ($this->instruction->isBulky()) {
//			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiMaskDeclaration));
//		} else {
//			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiMaskDeclaration));
//		}
//
//		return $result;
//	}
}