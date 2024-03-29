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
namespace rocket\user\model\security;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\entry\EiEntryConstraint;
use rocket\op\ei\manage\frame\CriteriaConstraint;
use rocket\op\ei\manage\security\EiEntryAccess;
use rocket\op\ei\manage\security\EiExecution;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\component\command\EiCmd;

class FullyGrantedEiExecution implements EiExecution {
// 	private $commandPath;
	private $eiCmd;
	private $eiEntryAccessFactory;
	
	public function __construct(EiCmd $eiCmd) {
// 		$this->commandPath = $commandPath;
		$this->eiCmd = $eiCmd;
	}
	
	public function getEiCmd(): EiCmd {
		return $this->eiCmd;
	}
	
	public function getCriteriaConstraint(): ?CriteriaConstraint {
		return null;
	}

	public function getEiEntryConstraint(): ?EiEntryConstraint {
		return null;
	}

	public function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return new StaticEiEntryAccess(true);
	}
}


class StaticEiEntryAccess implements EiEntryAccess {
	private $granted;
	
	public function __construct(bool $granted) {
		$this->granted = $granted;
	}
	
	public function isEiPropWritable(EiPropPath $eiPropPath): bool {
		return $this->granted;
	}

	public function isEiCommandExecutable(EiCmdPath $eiCmdPath): bool {
		return $this->granted;
	}
	public function getEiEntryConstraint(): ?EiEntryConstraint {
		return null;
	}


}
