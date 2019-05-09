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

class SiApiController extends ControllerAdapter {
	private $eiFrame;
	
	function prepare(ManageState $manageState) {
		$this->eiFrame = $manageState->peakEiFrame();
	}
	
	function index() {
		echo 'very apisch';
	}
	
	function doExecControl(ParamPost $apiCallId, ParamPost $entryInputMaps = null) {
		$siApiCallId = null;
		try {
			$siApiCallId = SiApiCallId::parse($apiCallId->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);	
		}
		
		$guiControlPath = $siApiCallId->getGuiControlPath();
		if (!$guiControlPath->startsWith($this->eiFrame->getEiExecution()->getEiCommandPath())) {
			throw new BadRequestException(null, null, $e);
		}
		
		$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$eiGui = $eiMask->createEiGui($this->eiFrame, $siApiCallId->getViewMode(), true);
		
		$guiControl = null;
		try {
			$guiControl = $eiGui->createGeneralGuiControl($guiControlPath);
		} catch (UnknownGuiControlException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
		
		if ($entryInputMaps !== null) {
			if (!$guiControl->isInputHandled()) {
				throw new BadRequestException('No input SiControl executed with input.');
			}
			
			$siInputFactory = new SiInputFactory($this->getRequest()->getUploadDefinitions());
			
			try {
				$this->handleInput($eiGui, $siInputFactory->create($entryInputMaps->parseJson()));
			} catch (AttributesException $e) {
				throw new BadRequestException(null, null, $e);
			}
		}
		
		$guiControl->handle($eiGui);
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
	 * @param EiGui $eiGui
	 * @param SiInput $siInput
	 */
	private function handleInput($eiGui, $siInput) {
		$contextEiEngine = $this->eiFrame->getContextEiEngine();
		
		foreach ($siInput->getEntryInputs() as $entryInput) {
			$eiObject = null;
			if (null !== $entryInput->getId()) {
				$eiObject = $this->lookupEiObject($entryInput->getId());
			} else {
				$eiObject = $this->createEiObject($entryInput->getBuildupId());
			}
			
			$eiEntry = $this->eiFrame->createEiEntry($eiObject);
			
			$eiEntryGui = $eiGui->createEiEntryGui($eiEntry, 0);
			
			$eiEntryGui->get
			
		}
	}
	
	function doExecEntryControl(ParamPost $siEntryId, ParamPost $apiCallId, ParamPost $bulky, ParamPost $inputMap) {
		$inputMap->parseJsonToAttributes();
	}
	
	function doExecSelectionControl(ParamPost $siEntryIds, ParamPost $apiCallId, ParamPost $bulky) {
		
	}
	
	function doLoadSiEntries(ParamPost $pids) {
		
	}
}