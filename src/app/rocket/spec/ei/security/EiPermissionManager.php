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
namespace rocket\spec\ei\security;

use rocket\spec\ei\component\command\EiCommand;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\mask\EiMask;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiEngineModel;

interface EiPermissionManager {

	/**
	 * @param EiCommand $eiCommand
	 * @return bool
	 */
	public function isEiCommandAccessible(EiCommand $eiCommand): bool;
	
	/**
	 * @param EiCommand $eiCommand
	 * @throws InaccessibleControlException
	 * @return \rocket\spec\ei\security\EiExecution
	 */
	public function createEiExecution(EiCommand $eiCommand, N2nContext $n2nContext): EiExecution;
	
	/**
	 * @param EiMask $eiMask
	 * @param EiCommandPath $commandPath
	 * @return \rocket\spec\ei\security\EiExecution
	 */
	public function createUnboundEiExceution(EiEngineModel $eiThing, EiCommandPath $commandPath, 
			N2nContext $n2nContext): EiExecution;
}
