<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiSetup;
use rocket\ei\util\Eiu;
use rocket\attribute\impl\EiMods;

#[EiType]
#[EiMods(ModTestMod::class)]
class ModTestObj {

	public int $id;
	public string $string;
}