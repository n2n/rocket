<?php

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
