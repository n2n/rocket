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

use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\gui\control\EntryGuiControl;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\frame\EiFrame;
use n2n\web\http\BadRequestException;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\si\control\SiResult;
use n2n\util\type\attrs\AttributesException;
use rocket\si\input\SiInput;
use rocket\si\input\SiError;
use rocket\si\input\SiEntryInput;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\util\ex\IllegalStateException;
use rocket\spec\TypePath;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\si\input\SiInputFactory;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\UnknownEiTypeException;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use n2n\web\http\ForbiddenException;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\gui\EiGui;

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
	 * @var EiGuiModel
	 */
	private $eiGuiModel;
	private $eiEntry;
	private $guiField;
	/**
	 * @var GuiControl
	 */
	private $guiControl;
	/**
	 * @var EntryGuiControl
	 */
	private $entryGuiControl;
	/**
	 * @var GeneralGuiControl
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
	 */
	function setupEiGuiFrame(int $viewMode, TypePath $eiTypePath) {
		try {
			$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->determineEiMask($eiTypePath);
			$this->eiGuiModel = $this->createEiGuiModel($eiMask, $viewMode);
			$this->eiGui = new EiGui($this->eiGuiModel);
		} catch (\rocket\ei\EiException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	private function createEiGuiModel(EiMask $eiMask, int $viewMode) {
		try {
			return $this->eiFrame->getManageState()->getEiGuiModelCache()->obtainEiGuiModel($eiMask, $viewMode, null);
		} catch (\rocket\ei\EiException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $pid
	 * @throws UnknownEiObjectException
	 */
	function determineEiEntry(string $pid) {
		$eiObject = $this->eiFrameUtil->lookupEiObject($pid);
		$this->eiEntry = $this->eiFrame->createEiEntry($eiObject);
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
			$eiEntryGui = $this->eiGui->appendEiEntryGui($this->eiFrame, [$this->eiEntry]);
			$this->guiField = $eiEntryGui->getSelectedTypeDef()->getGuiFieldByDefPropPath($defPropPath);
		} catch (GuiException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
		
	}
	
	function determineGuiControl(GuiControlPath $guiControlPath) {
		if (!$guiControlPath->startsWith(EiCommandPath::from($this->eiFrame->getEiExecution()->getEiCommand()))) {
			throw new BadRequestException();
		}
		
		try {
			if ($this->eiEntry !== null) {
				$this->entryGuiControl = $this->guiControl = $this->eiGuiModel
						->createEntryGuiControl($this->eiFrame, $guiControlPath);
			} else {
				$this->generalGuiControl = $this->guiControl = $this->eiGuiModel
						->createGeneralGuiControl($this->eiFrame, $guiControlPath);
			}
		} catch (UnknownGuiControlException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
	}
	
	/**
	 * @param array $data
	 * @throws BadRequestException
	 * @return \rocket\si\input\SiEntryError|null
	 */
	function handleInput(array $data) {
		if (!$this->guiControl->isInputHandled()) {
			throw new BadRequestException('No input SiControl executed with input.');
		}
		
		$inputFactory = new SiInputFactory();
		
		try {
			if (null !== ($err = $this->applyInput($inputFactory->create($data)))) {
				return (new SiResult())->setInputError($err);
			}
			
			return null;
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
	 * @return SiError|null
	 * @throws UnknownEiObjectException
	 * @throws UnknownEiTypeException
	 * @throws InaccessibleEiEntryException
	 * @throws \InvalidArgumentException
	 */
	private function applyInput($siInput) {
		$entryErrors = [];
		
		foreach ($siInput->getEntryInputs() as $key => $entryInput) {
			$eiEntryGui = null;
			if ($this->eiEntry !== null) {
				if ($this->eiEntry->getPid() !== $entryInput->getIdentifier()->getId()) {
					throw new \InvalidArgumentException('EntryInput id missmatch. Id: ' + $this->eiEntry->getPid() 
							. ' Entry Input Id: ' . $entryInput->getIdentifier()->getId());
				}
				
				$eiEntryGui = $this->eiGuiModel->createEiEntryGuiVariation($this->eiFrame, $this->eiEntry);
				$this->eiGui->addEiEntryGui($eiEntryGui);
			} else {
				$eiObject = null;
				if (null !== $entryInput->getIdentifier()->getId()) {
					$eiObject = $this->eiFrameUtil->lookupEiObject($entryInput->getIdentifier()->getId());
				} else {
					$eiObject = $this->eiFrameUtil->createNewEiObject($entryInput->getTypeId());
				}
				
				$this->inputEiEntries[] = $eiEntry = $this->eiFrame->createEiEntry($eiObject);
				$eiGuiModel = $this->createEiGuiModel($eiEntry->getEiMask(), $this->eiGuiModel->getViewMode());
				
				$eiEntryGui = $eiGuiModel->createEiEntryGui($this->eiFrame, [$eiEntry], $this->eiGui);
			}
			
			$this->applyEntryInput($entryInput, $eiEntryGui);
			
			if ($eiEntry->validate()) {
				continue;
			}
			
			$entryErrors[$key] = $eiEntry->getValidationResult()->toSiEntryError($this->eiFrame->getN2nContext()->getN2nLocale());
		}
		
		if (empty($entryErrors)) {
			return null;
		}
		
		return new SiError($entryErrors);
	}
	
	/**
	 * @param SiEntryInput $entryInput
	 * @param EiEntryGui $eiEntryGui
	 * @param \InvalidArgumentException
	 */
	private function applyEntryInput($entryInput, $eiEntryGui) {
		$eiEntryGui->handleSiEntryInput($entryInput);
		
		// 			foreach ($eiEntryGui->getGuiFieldForks() as $defPropPathStr => $guiFieldFork) {
		// 				$guiFieldFork->
		// 			}
		
		$eiEntryGui->save();
	}
	
	function callGuiControl() {
		if ($this->generalGuiControl !== null) {
			return $this->generalGuiControl->handle($this->eiFrame, $this->eiGuiModel, $this->inputEiEntries);
		}
		
		if ($this->entryGuiControl !== null) {
			return $this->entryGuiControl->handleEntry($this->eiFrame, $this->eiGuiModel, $this->eiEntry);
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