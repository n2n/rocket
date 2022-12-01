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

use rocket\user\bo\RocketUser;
use rocket\ei\component\command\EiCmdNature;
use rocket\ei\EiCmdPath;
use rocket\ei\EiPropPath;
use rocket\ei\manage\security\EiPermissionManager;
use rocket\spec\TypePath;
use rocket\user\bo\EiGrant;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\security\EiExecution;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\security\InaccessibleEiCmdPathException;
use rocket\ei\manage\entry\EiEntryConstraint;
use rocket\ei\component\command\EiCmd;

class FullEiPermissionManager implements EiPermissionManager {

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiPermissionManager::isEiCommandAccessible()
	 */
	function isEiCommandAccessible(EiMask $contextEiMask, EiCmd $eiCmd): bool {
		return true;
	}
	
	function createEiExecution(EiMask $contextEiMask, EiCmd $eiCmd): EiExecution {
		return new FullyGrantedEiExecution($eiCmd);
	}
}
