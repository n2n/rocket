<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use n2n\persistence\orm\attribute\OneToMany;

#[EiType]
#[EiPreset(EiPresetMode::READ, readProps: ['testObj1s'])]
class RelationTestObj2 {

	public int $id;

	#[OneToMany(RelationTestObj1::class)]
	public \ArrayObject $testObj1s;

}
