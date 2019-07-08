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
use rocket\ei\manage\EiObject;
use n2n\web\http\BadRequestException;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\si\input\SiInputFactory;
use rocket\si\control\SiResult;
use n2n\util\type\attrs\AttributesException;
use rocket\si\input\SiInput;
use rocket\si\input\SiError;
use rocket\si\input\SiEntryInput;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\util\ex\IllegalStateException;

class ApiControlProcess {
	private $eiFrame;
	/**
	 * @var ApiProcessUtil
	 */
	private $util;
	private $eiGui;
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
		$this->util = new ApiProcessUtil($eiFrame);
	}
	
	/**
	 * @param int $viewMode
	 */
	function setupEiGui(int $viewMode) {
		$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$this->eiGui = $eiMask->createEiGui($this->eiFrame, $viewMode, true);
	}
	
	/**
	 * @param string $buildupId
	 * @return EiObject
	 */
	private function createEiObject($buildupId) {
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		if ($eiType->getId() == $buildupId) {
			return $eiType->createNewEiObject(false);
		}
		
		try {
			return $eiType->getSubEiTypeById($buildupId)->createNewEiObject();
		} catch (\rocket\ei\UnknownEiTypeException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	
	function determineEiEntry(string $pid) {
		$eiObject = $this->lookupEiObject($pid);
		$this->eiEntry = $this->eiFrame->createEiEntry($eiObject);
	}
	
	function determineGuiField(GuiFieldPath $guiFieldPath) {
		$eiEntryGui = $this->eiGui->createEiEntryGui($eiEntry, 0);
		
		try {
			$this->guiField = $eiEntryGui->getGuiField($guiFieldPath);
		} catch (GuiException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
	}
	
	function determineGuiControl(GuiControlPath $guiControlPath) {
		if (!$guiControlPath->startsWith($this->eiFrame->getEiExecution()->getEiCommandPath())) {
			throw new BadRequestException(null, null, $e);
		}
		
		try {
			if ($this->eiEntry !== null) {
				$this->entryGuiControl = $this->guiControl = $this->eiGui->createEntryGuiControl($guiControlPath);
			} else {
				$this->generalGuiControl = $this->guiControl = $this->eiGui->createGeneralGuiControl($guiControlPath);
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
		}
	}
	
	/**
	 * @param SiInput $siInput
	 * @return SiError|null
	 */
	private function applyInput($siInput) {
		$entryErrors = [];
		
		foreach ($siInput->getEntryInputs() as $key => $entryInput) {
			$eiObject = null;
			if (null !== $entryInput->getId()) {
				$eiObject = $this->lookupEiObject($entryInput->getId());
			} else {
				$eiObject = $this->createEiObject($entryInput->getBuildupId());
			}
			
			$eiEntry = $this->eiFrame->createEiEntry($eiObject);
			
			$eiEntryGui = $this->eiGui->createEiEntryGui($eiEntry, 0);
			
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
	 */
	private function applyEntryInput($entryInput, $eiEntryGui) {
		
		// 			foreach ($eiEntryGui->getGuiFieldForks() as $guiFieldPathStr => $guiFieldFork) {
		// 				$guiFieldFork->
		// 			}
		
		foreach ($eiEntryGui->getGuiFields() as $guiFieldPathStr => $guiField) {
			if ($guiField->getSiField()->isReadOnly()
					|| !$entryInput->containsFieldName($guiFieldPathStr)) {
						continue;
					}
					
					$guiField->getSiField()->handleInput($entryInput->getFieldInput($guiFieldPathStr)->getData());
		}
		
		$eiEntryGui->save();
	}
	
	function callGuiControl() {
		if ($this->generalGuiControl !== null) {
			return $this->generalGuiControl->handle($this->eiGui);
		}
		
		if ($this->entryGuiControl !== null) {
			return $this->entryGuiControl->handleEntry($this->eiGui, $this->eiEntry);
		}
		
		throw new IllegalStateException();
	}
}