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

#[EiType]
#[EiPreset(EiPresetMode::EDIT_CMDS, editProps: ['holeradio', 'mandatoryHoleradio', 'holeradioObj', 'mandatoryHoleradioObj'])]
class StringTestObj {

	public int $id;
	public ?string $holeradio = null;
	public string $mandatoryHoleradio = 'holeradio';
	#[EiPropString(multiline: true, constant: true, readOnly: true, mandatory: true, minlength: 2, maxlength: 512)]
	public $annoHoleradio = 'asd';

	public ?StrObjMock $holeradioObj = null;
	public StrObjMock $mandatoryHoleradioObj;
	#[EiPropString(multiline: true, constant: true, readOnly: true, mandatory: true)]
	private ?StrObjMock $annoHoleradioObj;

	function __construct() {
		$this->mandatoryHoleradioObj = new StrObjMock('default');
		$this->annoHoleradioObj = new StrObjMock('default');
	}

	function getAnnoHoleradioObj(): ?StrObjMock {
		return $this->annoHoleradioObj;
	}
}