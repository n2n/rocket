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

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\entry\UnknownEiObjectException;
use n2n\web\http\BadRequestException;
use rocket\ei\manage\EiObject;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\input\SiEntryInput;
use rocket\ei\manage\security\SecurityException;

class ProcessUtil {
	private $eiFrame;
	
	/**
	 * @param EiFrame $eiFrame
	 */
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @param string $typeId
	 * @return EiObject
	 */
	function createEiObject(string $typeId) {
		$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
		if ($eiType->getId() == $typeId) {
			return $eiType->createNewEiObject(false);
		}
		
		try {
			return $eiType->getSubEiTypeById($typeId)->createNewEiObject();
		} catch (\rocket\ei\UnknownEiTypeException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param string $pid
	 * @return \rocket\ei\manage\EiEntityObj
	 */
	function lookupEiObject(string $pid) {
		try {
			$efu = new EiFrameUtil($this->eiFrame);
			return new LiveEiObject($efu->lookupEiEntityObj($efu->pidToId($pid)));
		} catch (UnknownEiObjectException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @return EiObject
	 */
	function determineEiObjectOfInput(SiEntryInput $siEntryInput) {
		if (null !== ($pid = $siEntryInput->getIdentifier()->getId())) {
			return $this->lookupEiObject($pid);
		}
		
		return $this->createEiObject($siEntryInput->getTypeId());
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @return EiObject
	 */
	function determineEiEntryOfInput(SiEntryInput $siEntryInput) {
		try {
			return $this->eiFrame->createEiEntry($this->determineEiObjectOfInput($siEntryInput));
		} catch (SecurityException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws BadRequestException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function determineEiEntryGuiOfInput(SiEntryInput $siEntryInput) {
		$eiObject = $this->determineEiObjectOfInput($siEntryInput);
		
		try {
			$efu = new EiFrameUtil($this->eiFrame);
			return $efu->createEiEntryGuiFromEiObject($eiObject, $siEntryInput->isBulky(), false);
		} catch (SecurityException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @param EiEntryGui $eiEntryGui
	 * @throws BadRequestException
	 * @return \rocket\si\input\SiEntryError|NULL
	 */
	function handleEntryInput(SiEntryInput $siEntryInput, EiEntryGui $eiEntryGui) {
		try {
			$eiEntryGui->handleSiEntryInput($siEntryInput);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, 0, $e);
		}
		
		$eiEntryGui->save();
		
		$eiEntry = $eiEntryGui->getEiEntry();
		
		if ($eiEntry->validate()) {
			return null;
		}
		
		return $eiEntry->getValidationResult()->toSiEntryError($this->eiFrame->getN2nContext()->getN2nLocale());
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\ei\mask\EiMask
	 */
	function determinEiMask(EiObject $eiObject) {
		return $this->eiFrame->getContextEiEngine()->getEiMask()
				->determineEiMask($eiObject->getEiEntityObj()->getEiType());
	}
	
	/**
	 * @param EiMask $eiMask
	 * @param int $viewMode
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function createEiGuiFrame(EiMask $eiMask, int $viewMode) {
		return $eiMask->getEiEngine()->createFramedEiGuiFrame($this->eiFrame, $viewMode);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param EiGuiFrame $eiGuiFrame
	 * @return EiEntryGui
	 */
	function createEiEntryGui(EiObject $eiObject, EiGuiFrame $eiGuiFrame) {
		return $eiGuiFrame->createEiEntryGui($this->eiFrame->createEiEntry($eiObject));
	}
}