<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiDefaultSort;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;

#[EiType]
#[EiDefaultSort(['holeradio' => 'ASC', 'num' => 'DESC'])]
#[EiPreset(EiPresetMode::READ)]
class SortTestObj {
	public int $id;
	public string $holeradio = 'dings';
	PUBLIC int $num = 0;
}