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
namespace rocket\op\ei\component\command;

use rocket\op\ei\component\EiComponentNature;
use rocket\op\ei\util\Eiu;
use n2n\util\ex\IllegalStateException;
use n2n\web\http\controller\Controller;
use n2n\l10n\Lstr;
use rocket\op\ei\manage\gui\GuiCommand;
use rocket\si\control\SiNavPoint;

interface EiCmdNature extends EiComponentNature {
	
	/**
	 * @return Lstr
	 */
	function getLabelLstr(): Lstr;
	
	/**
	 * @return bool
	 */
	function isPrivileged(): bool;
	
	/**
	 * @param Eiu $eiu
	 * @return Controller|null
	 */
	function lookupController(Eiu $eiu): ?Controller;
	
	/**
	 * @param mixed $obj
	 * @return boolean
	 */
	function equals($obj);

	function buildGuiCommand(Eiu $eiu): ?GuiCommand;

	function buildOverviewNavPoint(Eiu $eiu): ?SiNavPoint;

	function buildEditNavPoint(Eiu $eiu): ?SiNavPoint;

	function buildDetailNavPoint(Eiu $eiu): ?SiNavPoint;

	function buildAddNavPoint(Eiu $eiu): ?SiNavPoint;
}
