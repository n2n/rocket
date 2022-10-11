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
namespace rocket\ei\manage\security;

use rocket\ei\component\command\EiCmdNature;
use rocket\ei\EiCmdPath;
use rocket\ei\mask\EiMask;
use rocket\ei\component\command\EiCmd;

interface EiPermissionManager {

	/**
	 * @param EiCmdNature $eiCmd
	 * @return bool
	 */
	public function isEiCommandAccessible(EiMask $contextEiMask, EiCmd $eiCmd): bool;
	
// 	/**
// 	 * @param EiCommand $eiCmd
// 	 * @throws InaccessibleControlException
// 	 * @return \rocket\ei\manage\security\EiExecution
// 	 */
// 	public function createEiExecution(EiCommand $eiCmd, N2nContext $n2nContext): EiExecution;
	
	/**
	 * @param EiMask $eiMask
	 * @param EiCmdPath $commandPath
	 * @return \rocket\ei\manage\security\EiExecution
	 *@throws InaccessibleEiCmdPathException
	 */
	public function createEiExecution(EiMask $contextEiMask, EiCmd $eiCmd): EiExecution;
}
