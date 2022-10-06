<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;

#[EiType]
#[EiPreset(EiPresetMode::READ, readProps: ['stringGetTest'], editProps: ['stringEditablePriTest'])]
class PrimitiveReadPresetTestObj {

	public int $id;
	public string $stringPriTest;
	private ?string $stringNullPriTest = null;
	private string $stringEditablePriTest;


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
}
