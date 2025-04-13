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

namespace testmdl\string\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;
use rocket\attribute\impl\EiPropString;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiPropPathPart;

#[EiType]
#[EiPreset(EiPresetMode::EDIT_CMDS, editProps: ['name', 'pathPart', 'mandatoryPathPart', 'annoPathPart'])]
class PathPartTestObj {

	public int $id;
	public string $name = 'Holeradio';
	public ?string $pathPart = null;
	#[EiPropPathPart(baseProp: 'name')]
	public string $mandatoryPathPart = 'mandatory-holeradio';
	#[EiPropPathPart(baseProp: 'name', uniquePerProp: 'pathPart')]
	public mixed $uniquePerPathPart = 'unique-per-holeradio';

	#[EiPropPathPart(baseProp: 'name', uniquePerProp: 'pathPart', constant: true, readOnly: true, mandatory: true)]
	public mixed $annoPathPart = 'anno-holeradio';

	function __construct() {
	}

}