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

use rocket\si\api\SiGetResponse;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\api\SiGetInstruction;
use rocket\si\api\SiGetResult;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\EiObject;
use rocket\si\api\SiPartialContentInstruction;

class GetInstructionProcess {
	private $instruction;
	private $util;
	private $apiUtil;
	private $eiFrameUtil;
	
	function __construct(SiGetInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ApiProcessUtil($eiFrame);
		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	/**
	 * @return SiGetResponse 
	 */
	function exec() {
		if (null !== ($entryId = $this->instruction->getEntryId())) {
			return $this->handleEntryId($entryId);
		}
		
		if ($this->instruction->isNewEntryRequested()) {
			return $this->handleNewEntry();
		}
		
		if (null !== ($spci = $this->instruction->getPartialContentInstruction())) {
			return $this->handlePartialContent($spci);
		}
	}
	
	private function handleEntryId(string $entryId) {
		$eiObject = $this->util->lookupEiObject($entryId);
		
		$eiEntryGui = $this->eiFrameUtil->createEiEntryGuiFromEiObject($eiObject, 
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
		
		return $this->createEntryResult([$eiEntryGui]);
	}
	
	private function handleNewEntry() {
		$eiEntryGuis = $this->eiFrameUtil->createPossibleNewEiEntryGuis(
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
				
		return $this->createEntryResult($eiEntryGuis);	
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param EiEntryGui[] $eiEntryGuis
	 * @return \rocket\si\api\SiGetResult
	 */
	private function createEntryResult($eiObject, array $eiEntryGuis) {
		$result = new SiGetResult();
		$result->setEntry($this->apiUtil->createSiEntry($eiObject, $eiEntryGuis));
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setBulkyDeclaration($this->apiUtil->createMulitBuildupSiBulkyDeclaration($eiEntryGuis));
		} else {
			$result->setCompactDeclaration($this->apiUtil->createMulitBuildupSiCompactDeclaration($eiEntryGuis));
		}
		
		return $result;
	}
	
	
	
	private function handlePartialContent(SiPartialContentInstruction $spci) {
		$num = $this->eiFrameUtil->count();
		$eiGui = $this->eiFrameUtil->lookupEiGuiFromRange($spci->getFrom(), $spci->getNum(),
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
		
		$result = new SiGetResult();
		$result->setPartialContent($this->apiUtil->createSiPartialContent($spci->getFrom(), $num, $eiGui));
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setBulkyDeclaration($this->apiUtil->createSiBulkyDeclaration($eiGui));
		} else {
			$result->setCompactDeclaration($this->apiUtil->createSiCompactDeclaration($eiGui));
		}
		
		return $result;
	}
}