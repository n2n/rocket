<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\ei\util\Eiu;

#[EiType]
#[EiPreset(EiPresetMode::READ, readProps: ['stringGetTest'], editProps: ['stringEditablePriTest' => 'Super duper label'])]
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

	#[EiSetup]
	private static function setup(Eiu $eiu) {

	}
}
