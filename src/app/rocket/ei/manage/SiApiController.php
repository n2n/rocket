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
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use n2n\web\http\BadRequestException;
use rocket\ei\manage\gui\control\GuiControlPath;
use n2n\util\type\attrs\AttributesException;
use rocket\si\input\SiInput;
use rocket\si\input\SiInputFactory;

class SiApiController extends ControllerAdapter {
	private $eiFrame;
	
	function setEiFrame(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	function index() {
		echo 'very apisch';
	}
	
	function doExecGeneralGuiControl(ParamPost $siControlId, ParamPost $inputMap = null) {
		$eiMask = $this->eiFrame->getContextEiEngine()->getEiMask();
		$eiGui = $eiMask->createEiGui($eiFrame, $viewMode, $init);
		
		$guiControl = null;
		try {
			$guiControl = $eiGui->createGeneralGuiControl(GuiControlPath::create($siControlId->toNotEmptyString()));
		} catch (UnknownGuiControlException $e) {
			throw new BadRequestException($e->getMessage(), null, $e);
		}
		
		if ($inputMap !== null) {
			if (!$siControl->isInputHandled()) {
				throw new BadRequestException('No input SiControl executed with input.');
			}
			
			$siInputFactory = new SiInputFactory([]);
			
			try {
				$this->handleInput($eiGui, $siInputFactory->create($inputMap->parseJson()));
			} catch (AttributesException $e) {
				throw new BadRequestException(null, null, $e);
			}
		}
		
		$siControl->handle($eiGui);
	}
	
	
	
	function doExecEntryGuiControl(ParamPost $siEntryId, ParamPost $siControlId, ParamPost $inputMap) {
		$inputMap->parseJsonToAttributes();
	}
	
	function doExecSelectionGuiControl(ParamPost $siEntryIds, ParamPost $siControlId) {
		
	}
	
	function doLoadSiEntries(ParamPost $pids) {
		
	}
}