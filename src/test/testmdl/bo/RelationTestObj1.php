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

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use n2n\persistence\orm\attribute\ManyToOne;
use n2n\impl\persistence\orm\property\relation\Relation;

#[EiType]
#[EiPreset(EiPresetMode::READ, editProps: ['testObj2'])]
class RelationTestObj1 {

	private int $id;
	#[ManyToOne(RelationTestObj2::class)]
	private RelationTestObj2 $testObj2;

	function getId() {
		return $this->id;
	}

	function getTestObj2() {
		return $this->testObj2;
	}

	function setTestObj2(RelationTestObj2 $testObj2) {
		$this->testObj2 = $testObj2;
	}

}
