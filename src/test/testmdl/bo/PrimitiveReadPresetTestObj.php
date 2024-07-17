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
use rocket\attribute\EiLabel;

#[EiType]
#[EiPreset(EiPresetMode::READ, readProps: ['stringGetTest'],
		editProps: ['stringEditablePriTest' => 'Super duper label', 'stringEditableNullNotNullTest'])]
class PrimitiveReadPresetTestObj {

	public int $id;
	#[EiLabel('Test Label', 'Test Help Text')]
	public string $stringPriTest;
	private ?string $stringNullPriTest = null;
	private string $stringEditablePriTest;
	private ?string $stringEditableNullNotNullTest = null;

	public bool $boolPubTest;


	public function getStringNullPriTest(): ?string {
		return $this->stringNullPriTest;
	}

	public function getStringEditablePriTest(): string {
		return $this->stringEditablePriTest;
	}

	public function setStringEditablePriTest(string $stringEditablePriTest): void {
		$this->stringEditablePriTest = $stringEditablePriTest;
	}

	function getStringGetTest(): string {
		return 'huiii';
	}

	public function getStringEditableNullNotNullTest(): ?string {
		return $this->stringEditableNullNotNullTest;
	}

	public function setStringEditableNullNotNullTest(string $stringEditableNullNotNullTest): void {
		$this->stringEditableNullNotNullTest = $stringEditableNullNotNullTest;
	}



	#[EiSetup]
	private static function setup(Eiu $eiu) {

	}
}
