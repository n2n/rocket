<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\ei\util\Eiu;
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
