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
namespace rocket\ei\manage\api;

use rocket\si\api\SiValResponse;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\api\SiValResult;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\api\SiPartialContentInstruction;
use rocket\si\content\SiEntry;
use rocket\si\api\SiValInstruction;
use rocket\si\input\SiEntryInput;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\ViewMode;
use rocket\si\api\SiValGetResult;
use rocket\si\api\SiValGetInstruction;

class ValInstructionProcess {
	private $instruction;
	private $util;
	private $apiUtil;
	private $eiFrameUtil;
	
	private $eiEntry = null;
	private $eiEntryGuis = null;
	
	function __construct(SiValInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ProcessUtil($eiFrame);
		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	function clear() {
		$this->eiEntry = null;
		$this->eiEntryGuis = [];
	}
	
	/**
	 * @return SiValResponse 
	 */
	function exec() {
		IllegalStateException::assertTrue($this->eiEntry === null);
		
		$entryInput = $this->instruction->getEntryInput();
		
		$eiEntryGui = $this->util->determineEiEntryGuiOfInput($entryInput);
		$this->eiEntry = $eiEntryGui->getEiEntry();
		$this->registerEiEntryGui($eiEntryGui);
		
		$result = new SiValResult();
		$result->setEntryError($this->util->handleEntryInput($entryInput, $eiEntryGui));
		
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
		$eiEntryGui = $this->obtainEiEntryGui($getInstruction->isBulky(), $getInstruction->isReadOnly());
		
		$result = new SiValGetResult();
		$result->setEntry($eiEntryGui->createSiEntry($getInstruction->areControlsIncluded()));
		
		if ($getInstruction->isDeclarationRequested()) {
			$result->setDeclaration($eiEntryGui->getEiGuiFrame()->createSiDeclaration());
		}
		
		return $result;
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	private function registerEiEntryGui($eiEntryGui) {
		$this->eiEntryGuis[$eiEntryGui->getEiGuiFrame()->getViewMode()] = $eiEntryGui;
	}
	
	/**
	 * @param int $viewMode
	 * @return EiEntryGui
	 */
	private function obtainEiEntryGui(bool $bulky, bool $readOnly) {
		$viewMode = ViewMode::determine($bulky, $readOnly, $this->eiEntry->isNew());
		if (isset($this->eiEntryGuis[$viewMode])) {
			return $this->eiEntryGuis[$viewMode];
		}
		
		$eiEntryGui = $this->eiFrameUtil->createEiEntryGui($this->eiEntry, $bulky, $readOnly);
		$this->registerEiEntryGui($eiEntryGui);
		return $eiEntryGui;
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
		$eiEntryGuiMulti = $this->eiFrameUtil->createNewEiEntryGuiMulti(
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
				
		return $this->createEntryResult($eiEntryGuiMulti->createSiEntry(), $eiEntryGuiMulti->getEiEntryGuis());	
	}
	
	/**
	 * @param SiEntry $siEntry
	 * @param EiEntryGui[] $eiEntryGuis
	 * @return \rocket\si\api\SiValResult
	 */
	private function createEntryResult(SiEntry $siEntry, array $eiEntryGuis) {
		$result = new SiValResult();
		$result->setEntry($siEntry);
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiEntryGuis));
		} else {
			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiEntryGuis));
		}
		
		return $result;
	}
	
	private function handlePartialContent(SiPartialContentInstruction $spci) {
		$num = $this->eiFrameUtil->count();
		$eiGuiFrame = $this->eiFrameUtil->lookupEiGuiFrameFromRange($spci->getFrom(), $spci->getNum(),
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
		
		$result = new SiValResult();
		$result->setPartialContent($this->apiUtil->createSiPartialContent($spci->getFrom(), $num, $eiGuiFrame));
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiFrame));
		} else {
			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiFrame));
		}
		
		return $result;
	}
}