<?php

namespace testmdl\relation\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiDefaultSort;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class IntegratedTargetTestObj {
	public $id;
	public string $dingsel = 'str';
}