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
use rocket\si\api\SiGetInstruction;
use rocket\ui\si\api\SiGetResult;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\ui\gui\EiGuiValueBoundary;
use rocket\ui\si\api\SiPartialContentInstruction;
use rocket\op\ei\manage\DefPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\web\http\BadRequestException;
use rocket\ui\si\content\SiPartialContent;

class GetInstructionProcess {

	private $instruction;
	private $util;
	private $apiUtil;
	private $eiFrameUtil;
	
	function __construct(SiGetInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ProcessUtil($eiFrame);
		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	

	function exec(): SiGetResult {
		if (null !== ($entryId = $this->instruction->getEntryId())) {
			return $this->handleEntryId($entryId);
		}
		
		if ($this->instruction->isNewEntryRequested()) {
			return $this->handleNewEntry();
		}
		
		if (null !== ($spci = $this->instruction->getPartialContentInstruction())) {
			return $this->handlePartialContent($spci);
		}
		
		throw new IllegalStateException();
	}
	
	/**
	 * @return NULL|DefPropPath[]
	 */
	private function parseDefPropPaths() {
		$propIds = $this->instruction->getPropIds();
		
		if ($propIds === null) {
			return null;
		}
		
		return array_map(function ($propId) {
			return DefPropPath::create($propId);
		}, $propIds);
	}

	/**
	 * @throws BadRequestException
	 */
	private function handleEntryId(string $entryId): SiGetResult {
		$defPropPaths = $this->parseDefPropPaths();
		
		$eiGuiValueBoundary = $this->util->lookupEiGuiByPid($entryId, $this->instruction->getStyle()->isBulky(),
				$this->instruction->getStyle()->isReadOnly(), $this->instruction->areEntryControlsIncluded(), $defPropPaths);

		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$getResult = new SiGetResult();
		$getResult->setValueBoundary($eiGuiValueBoundary
				->createSiValueBoundary($this->eiFrameUtil->getEiFrame()->getN2nContext()->getN2nLocale()));
		
//		if ($this->instruction->areGeneralControlsIncluded()) {
//			$getResult->setGeneralControls($eiGuiValueBoundary->getEiGuiDeclaration()->createGeneralSiControls($eiFrame));
//		}

		if ($this->instruction->areGeneralControlsIncluded() && $eiGuiValueBoundary->isEiGuiEntrySelected()) {
			$getResult->setGeneralControls($eiGuiValueBoundary->getSelectedEiGuiEntry()->getEiGuiMaskDeclaration()
					->createGeneralSiControls($eiFrame));
		}
		
		if ($this->instruction->isDeclarationRequested()) {
			$getResult->setDeclaration($eiGuiValueBoundary->getEiGuiDeclaration()
					->createSiDeclaration($eiFrame->getN2nContext()->getN2nLocale()));
		}

		return $getResult;
	}
	
	/**
	 * @return SiGetResult
	 */
	private function handleNewEntry(): SiGetResult {
		$defPropPaths = $this->parseDefPropPaths();

		$eiGuiDeclaration = $this->eiFrameUtil->createNewEiGuiDeclaration(
				$this->instruction->getStyle()->isBulky(), $this->instruction->getStyle()->isReadOnly(), $defPropPaths,
				$this->instruction->getTypeIds());
		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$getResult = new SiGetResult();
		$getResult->setValueBoundary($eiGuiDeclaration
				->createNewGuiValueBoundary($eiFrame, $this->instruction->areEntryControlsIncluded())
				->getSiValueBoundary($eiFrame->getN2nContext()->getN2nLocale()));
		
		if ($this->instruction->areGeneralControlsIncluded() && $eiGuiDeclaration->hasSingleEiGuiMaskDeclaration()) {
			$getResult->setGeneralControls($eiGuiDeclaration->getSingleEiGuiMaskDeclaration()
					->createGeneralGuiControlsMap($eiFrame)->createSiControls());
		}
		
		if ($this->instruction->isDeclarationRequested()) {
			$getResult->setDeclaration($eiGuiDeclaration->createSiDeclaration($eiFrame->getN2nContext()->getN2nLocale()));
		}
		
		return $getResult;
	}
	

//	private function createEntryResult(SiValueBoundary $siValueBoundary, array $eiGuiValueBoundaries): SiGetResult {
//		$result = new SiGetResult();
//		$result->setValueBoundary($siValueBoundary);
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
	
	private function handlePartialContent(SiPartialContentInstruction $spci): SiGetResult {
		$num = $this->eiFrameUtil->count($spci->getQuickSearchStr());
		$rangeResult = $this->eiFrameUtil->lookupEiGuiFromRange($spci->getFrom(), $spci->getNum(),
				$this->instruction->getStyle()->isBulky(), $this->instruction->getStyle()->isReadOnly(),
				$this->instruction->areEntryControlsIncluded(), $this->parseDefPropPaths(), $spci->getQuickSearchStr());
		
		$result = new SiGetResult();

		$siPartialContent = new SiPartialContent($num);
		$siPartialContent->setOffset($spci->getFrom());
		$siPartialContent->setValueBoundaries(
				array_map(fn (EiGuiValueBoundary $b)
						=> $b->createSiValueBoundary($this->eiFrameUtil->getEiFrame()->getN2nContext()->getN2nLocale()),
				$rangeResult->guiValueBoundaries));
		$result->setPartialContent($siPartialContent);
		
		if ($this->instruction->areGeneralControlsIncluded()) {
			$result->setGeneralControls($rangeResult->guiDeclaration->createGeneralSiControls($this->eiFrameUtil->getEiFrame()));
		}
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		$result->setDeclaration($rangeResult->guiDeclaration
				->createSiDeclaration($this->eiFrameUtil->getEiFrame()->getN2nContext()->getN2nLocale()));
		
		return $result;
	}
	
	
	
}