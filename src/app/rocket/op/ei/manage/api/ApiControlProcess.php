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

use rocket\op\ei\EiException;
use rocket\op\ei\manage\gui\control\GuiControl;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\web\http\BadRequestException;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\GuiException;
use rocket\op\ei\manage\gui\control\GuiControlPath;
use rocket\op\ei\manage\gui\control\UnknownGuiControlException;
use n2n\util\type\attrs\AttributesException;
use rocket\si\input\SiInput;
use rocket\si\input\SiEntryInput;
use rocket\op\ei\manage\gui\EiGuiValueBoundary;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\si\input\SiInputFactory;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\ei\UnknownEiTypeException;
use rocket\op\ei\manage\security\InaccessibleEiEntryException;
use n2n\web\http\ForbiddenException;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use rocket\op\ei\manage\gui\EiGui;
use rocket\si\input\SiInputError;
use rocket\op\ei\manage\entry\EiEntry;
use n2n\util\ex\NotYetImplementedException;
use rocket\si\input\SiInputResult;

class ApiControlProcess {
	private $eiFrame;
	/**
	 * @var ProcessUtil
	 */
	private $util;
	/**
	 * @var EiFrameUtil
	 */
	private $eiFrameUtil;
	/**
	 * @var EiGui
	 */
	private $eiGui;
	/**
	 * @var EiGuiDeclaration
	 */
	private $eiGuiDeclaration;
	private $eiEntry;
	private $guiField;
	/**
	 * @var GuiControl
	 */
	private $guiControl;
	/**
	 * @var GuiControl
	 */
	private $entryGuiControl;
	/**
	 * @var GuiControl
	 */
	private $generalGuiControl;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
		$this->util = new ProcessUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	/**
	 * @param int $viewMode
     * @param string $eiTypeId
	 */
	function determineEiGuiMaskDeclaration(int $viewMode, string $eiTypeId) {
		try {
			$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->determineEiTypeById($eiTypeId);
			$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMask($eiType);
			$this->eiGuiDeclaration = $this->createEiGuiDeclaration($eiMask, $viewMode);
			$this->eiGui = new EiGui($this->eiGuiDeclaration);
		} catch (EiException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	private function createEiGuiDeclaration(EiMask $eiMask, int $viewMode) {
		try {
			return $eiMask->getEiEngine()->obtainEiGuiDeclaration($viewMode, null);
		} catch (EiException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $pid
	 * @throws UnknownEiObjectException
	 * @return EiEntry
	 */
	function determineEiEntry(string $pid) {
		$eiObject = $this->eiFrameUtil->lookupEiObject($this->eiFrameUtil->pidToId($pid));
		return $this->eiEntry = $this->eiFrame->createEiEntry($eiObject);
	}
	
	/**
	 * @param string $eiTypeId
	 * @throws UnknownEiTypeException
	 */
	function determineNewEiEntry(string $eiTypeId) {
		$eiObject = $this->eiFrameUtil->createNewEiObject($eiTypeId);
		$this->eiEntry = $this->eiFrame->createEiEntry($eiObject);
	}
	
	function determineGuiField(DefPropPath $defPropPath) {
		
		try {
			$eiGuiValueBoundary = $this->eiGui->appendEiGuiValueBoundary($this->eiFrame, [$this->eiEntry]);
			$this->guiField = $eiGuiValueBoundary->getSelectedEiGuiEntry()->getGuiFieldByDefPropPath($defPropPath);
		} catch (GuiException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
		
	}
	
	function determineGuiControl(GuiControlPath $guiControlPath) {
		if (!$guiControlPath->startsWith(EiCmdPath::from($this->eiFrame->getEiExecution()->getEiCmd()))) {
			throw new BadRequestException();
		}
		
		try {
			if ($this->eiEntry !== null) {
				$this->entryGuiControl = $this->guiControl = $this->eiGuiDeclaration
						->createEntryGuiControl($this->eiFrame, $this->eiEntry, $guiControlPath);
			} else {
				$this->generalGuiControl = $this->guiControl = $this->eiGuiDeclaration
						->createGeneralGuiControl($this->eiFrame, $guiControlPath);
			}
		} catch (UnknownGuiControlException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
	}
	
	/**
	 * @param array $data
	 * @throws BadRequestException
	 * @return \rocket\si\input\SiInputError|null
	 */
	function handleInput(array $data) {
		if (!$this->guiControl->isInputHandled()) {
			throw new BadRequestException('No input SiControl executed with input.');
		}
		
		$inputFactory = new SiInputFactory();
		
		try {
			return $this->applyInput($inputFactory->create($data));
		} catch (AttributesException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (UnknownEiObjectException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (UnknownEiTypeException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (InaccessibleEiEntryException $e) {
			throw new ForbiddenException(null, null, $e);
		}
	}
	
	private $inputEiEntries = [];
	
	/**
	 * @param SiInput $siInput
	 * @return SiInputError|null
	 * @throws UnknownEiObjectException
	 * @throws UnknownEiTypeException
	 * @throws InaccessibleEiEntryException
	 * @throws \InvalidArgumentException
	 */
	private function applyInput($siInput) {
		$siEntries = [];
		
		foreach ($siInput->getEntryInputs() as $key => $entryInput) {
			$eiGuiValueBoundary = null;
			if ($this->eiEntry !== null) {
				if ($this->eiEntry->getPid() !== $entryInput->getIdentifier()->getId()) {
					throw new \InvalidArgumentException('EntryInput id missmatch. Id: ' + $this->eiEntry->getPid() 
							. ' Entry Input Id: ' . $entryInput->getIdentifier()->getId());
				}
				
				$this->inputEiEntries[$key] = $this->eiEntry;
				$eiGuiValueBoundary = $this->eiGuiDeclaration->createEiGuiValueBoundaryVariation($this->eiFrame, $this->eiEntry);
				$this->eiGui->addEiGuiValueBoundary($eiGuiValueBoundary);
			} else {
				$eiObject = null;
				if (null !== $entryInput->getIdentifier()->getId()) {
					$eiObject = $this->eiFrameUtil->lookupEiObject($entryInput->getIdentifier()->getId());
				} else {
					$eiObject = $this->eiFrameUtil->createNewEiObject($entryInput->getMaskId());
				}
				
				$this->inputEiEntries[$key] = $eiEntry = $this->eiFrame->createEiEntry($eiObject);
				$eiGuiValueBoundary = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame, [$eiEntry], $this->eiGui);
			}
			
			$this->inputEiGuiValueBoundary[$key] = $eiGuiValueBoundary;
			
			$this->applyEntryInput($entryInput, $eiGuiValueBoundary);
			
			if ($eiEntry->validate()) {
				continue;
			}
			
			$siEntries[$key] = $this->eiGuiDeclaration->createSiEntry($this->eiFrame, $eiGuiValueBoundary, false);
		}
		
		if (empty($siEntries)) {
			return null;
		}
		
		return new SiInputError($siEntries);
	}
	
	/**
	 * @return \rocket\si\input\SiInputResult
	 */
	function createSiInputResult() {
		$siEntries = [];
		foreach ($this->inputEiGuiValueBoundaries as $key => $inputEiGuiValueBoundary) {
			$eiEntry = $this->eiFrameUtil->getEiFrame()->createEiEntry($this->inputEiEntries[$key]->getEiObject());
			$eiGuiDeclaration = $inputEiGuiValueBoundary->getEiGui()->getEiGuiDeclaration();
			$eiGui = new EiGui($eiGuiDeclaration);
			$eiGui->appendEiGuiValueBoundary($this->eiFrameUtil->getEiFrame(), [$eiEntry]);
			$siEntries[$key] = $eiGui->createSiEntry($this->eiFrameUtil->getEiFrame());
		}
		return new SiInputResult($siEntries);
	}
	
	/**
	 * @param SiEntryInput $entryInput
	 * @param EiGuiValueBoundary $eiGuiValueBoundary
	 * @param \InvalidArgumentException
	 */
	private function applyEntryInput($entryInput, $eiGuiValueBoundary) {
		$eiGuiValueBoundary->handleSiEntryInput($entryInput);
		
		// 			foreach ($eiGuiValueBoundary->getGuiFieldForks() as $defPropPathStr => $guiFieldFork) {
		// 				$guiFieldFork->
		// 			}
		
		$eiGuiValueBoundary->save();
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\si\control\SiCallResponse
	 */
	function callGuiControl() {
		if ($this->generalGuiControl !== null) {
			return $this->generalGuiControl->handle($this->eiFrame, $this->eiGuiDeclaration, $this->inputEiEntries);
		}
		
		if ($this->entryGuiControl !== null) {
			return $this->entryGuiControl->handleEntry($this->eiFrame, $this->eiGuiDeclaration, $this->eiEntry);
		}
		
		throw new IllegalStateException();
	}
	
	function callSiField(array $data, array $uploadDefinitions) {
		if ($this->guiField !== null) {
			return $this->guiField->getSiField()->handleCall($data, $uploadDefinitions);
		}
		
		throw new IllegalStateException();
	}
}