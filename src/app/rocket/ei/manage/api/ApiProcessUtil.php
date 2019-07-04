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
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiEntryGui;

class ApiProcessUtil {
	private $eiFrame;
	
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
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
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createEiGui(EiMask $eiMask, int $viewMode) {
		return $eiMask->getEiEngine()->createFramedEiGui($this->eiFrame, $viewMode);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param EiGui $eiGui
	 * @return EiEntryGui
	 */
	function createEiEntryGui(EiObject $eiObject, EiGui $eiGui) {
		return $eiGui->createEiEntryGui($this->eiFrame->createEiEntry($eiObject));
	}
}