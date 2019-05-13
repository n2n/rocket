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
namespace rocket\ei\manage;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamPost;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use n2n\web\http\BadRequestException;
use n2n\util\type\attrs\AttributesException;
use rocket\si\input\SiInputFactory;
use rocket\ei\manage\gui\EiGui;
use rocket\si\input\SiInput;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\frame\EiFrame;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\si\input\SiEntryInput;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\input\SiInputError;

class SiApiController extends ControllerAdapter {
	private $eiFrame;
	
	function prepare(ManageState $manageState) {
		$this->eiFrame = $manageState->peakEiFrame();
	}
	
	function index() {
		echo 'very apisch';
	}
	
	private function parseApiCallId(ParamQuery $paramQuery) {
		try {
			return SiApiCallId::parse($paramQuery->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	function doExecControl(ParamPost $apiCallId, ParamPost $entryInputMaps = null) {
		$siApiCallId = $this->parseApiCallId($apiCallId);
		
		$callProcess = new ApiControlProcess($this->eiFrame);
		$callProcess->determineGuiControl($apiCallId->getGuiControlPath());
		$callProcess->setupGui($apiCallId->getViewMode());
		
		$guiControl = null;
		
		
		if ($entryInputMaps !== null) {
			$callProcess->handleInput($entryInputMaps->parseJson());
			
			
		}
		
		$guiControl->handle($eiGui);
	}
	
	
	
	function doExecEntryControl(ParamPost $siEntryId, ParamPost $apiCallId, ParamPost $bulky, ParamPost $inputMap) {
		$inputMap->parseJsonToAttributes();
	}
	
	function doExecSelectionControl(ParamPost $siEntryIds, ParamPost $apiCallId, ParamPost $bulky) {
		
	}
	
	function doLoadSiEntries(ParamPost $pids) {
		
	}
}

class ApiControlProcess {
	private $eiFrame;
	private $guiControl;
	private $eiGui;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	function determineGuiControl(GuiControlPath $guiControlPath) {
		if (!$guiControlPath->startsWith($this->eiFrame->getEiExecution()->getEiCommandPath())) {
			throw new BadRequestException(null, null, $e);
		}
		
		try {
			$this->guiControl = $eiGui->createGeneralGuiControl($guiControlPath);
		} catch (UnknownGuiControlException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
	}
	
	/**
	 * @param int $viewMode
	 */
	function setupEiGui(int $viewMode) {
		$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$this->eiGui = $eiMask->createEiGui($this->eiFrame, $viewMode, true);
	}
	
	/**
	 * @param string $pid
	 * @return \rocket\ei\manage\EiEntityObj
	 */
	private function lookupEiObject($pid) {
		$efu = new EiFrameUtil($this->eiFrame);
		return $efu->lookupEiEntityObj($efu->pidToId($pid));
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
	
	/**
	 * @param array $data
	 * @throws BadRequestException
	 * @return \rocket\si\input\SiEntryInputError|null
	 */
	function handleInput(array $data) {
		if (!$this->guiControl->isInputHandled()) {
			throw new BadRequestException('No input SiControl executed with input.');
		}
		
		$inputFactory = new SiInputFactory();
		$inputFactory->registerUploadDefinitions($this->getRequest()->getUploadDefinitions());
		
		if ($inputFactory->hasErrors()) {
			return $inputFactory->createInputError();			
		}
		
		try {
			$this->applyInput($this->eiGui, $inputFactory->create($data));
		} catch (AttributesException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		foreach ($this->eiGui->getEiEntryGuis() as $eiEntryGui) {
			
			
			
		}
	}
	
	/**
	 * @param EiGui $eiGui
	 * @param SiInput $siInput
	 */
	private function applyInput($siInput) {
		$entryInputErrors = [];
		
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
			
			$entryInputErrors[$key] = $eiEntry->getValidationResult()->toSiEntryInputError();
		}
		
		if (empty($entryInputErrors)) {
			return null;
		}
		
		return new SiInputError($entryInputErrors);
	}
	
	/**
	 * @param SiEntryInput $entryInput
	 * @param EiEntryGui $eiEntryGui
	 */
	private function applyEntryInput($entryInput, $eiEntryGui) {
		
		// 			foreach ($eiEntryGui->getGuiFieldForks() as $guiFieldPathStr => $guiFieldFork) {
		// 				$guiFieldFork->
		// 			}
		
		foreach ($eiEntryGui->getGuiField() as $guiFieldPathStr => $guiField) {
			if ($guiField->getSiField()->isReadOnly()
					|| !$entryInput->containsFieldName($guiFieldPathStr)) {
				continue;
			}
			
			$guiField->getSiField()->handleInput($entryInput->getFieldData($guiFieldPathStr));
		}
		
		$eiEntryGui->save();
	}
}