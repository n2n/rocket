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

namespace rocket\op\spec\result;

use rocket\op\ei\component\command\EiCmdNature;
use rocket\op\ei\EiCmdPath;
use rocket\op\spec\TypePath;

class EiCommandError {
	private $eiTypePath;
	private $eiCmdPath;
	private $eiCmd;
	private $t;
	
	public function __construct(TypePath $eiTypePath, EiCmdPath $eiCmdPath, \Throwable $t,
			EiCmdNature $eiCmd = null) {
		$this->eiTypePath = $eiTypePath;
		$this->eiCmdPath = $eiCmdPath;
		$this->eiCmd = $eiCmd;
		$this->t = $t;
	}
	
	public function getEiCmdPath() {
		return $this->eiCmdPath;
	}
	
	public function getEiCommand() {
		return $this->eiCmd;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	/**
	 * @return \Throwable
	 */
	public function getThrowable() {
		return $this->t;
	}
	
	public static function fromEiCommand(EiCmdNature $eiCmd, \Throwable $t) {
		$wrapper = $eiCmd->getWrapper();
		return new EiCommandError($wrapper->getEiCommandCollection()->getEiMask()->getEiTypePath(),
				$wrapper->getEiCmdPath(), $t, $eiCmd);
	}
}