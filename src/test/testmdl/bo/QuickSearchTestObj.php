<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiDefaultSort;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class QuickSearchTestObj {
	public $id;
	public string $holeradio = 'dings';
	public string $holeradio2 = 'dingsel';
}
