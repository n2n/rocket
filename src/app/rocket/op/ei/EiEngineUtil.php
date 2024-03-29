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
namespace rocket\op\ei;

use rocket\op\ei\manage\EiObject;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\manage\EiLaunch;

class EiEngineUtil {
	private $eiEngine;
	private $eiLaunch;
	
	function __construct(EiEngine $eiEngine, EiLaunch $eiLaunch) {
		$this->eiEngine = $eiEngine;
		$this->eiLaunch = $eiLaunch;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\op\ei\EiEngine
	 */
	function determineEiEngine(EiObject $eiObject) {
		return $this->eiEngine->getEiMask()->determineEiMask($eiObject->getEiEntityObj()->getEiType())
				->getEiEngine();
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $determineEiMask
	 * @return string
	 */
	function createIdName(EiObject $eiObject, bool $determineEiMask = true) {
		$eiMask = $this->eiEngine->getEiMask(); 
		if ($determineEiMask) {
			$eiMask = $eiMask->determineEiMask($eiObject->getEiEntityObj()->getEiType());
		}
		
		$n2nContext = $this->eiLaunch->getN2nContext();
		return $this->eiLaunch->getDef()->getIdNameDefinition($eiMask)
				->createIdentityString($eiObject, $n2nContext, $n2nContext->getN2nLocale());
	}
}