<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiDefaultSort;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;

#[EiType]
#[EiDefaultSort(['holeradio' => 'ASC', 'num' => 'DESC'])]
#[EiPreset(EiPresetMode::READ)]
class SortTestObj {
	public int $id;
	public ?string $holeradio = 'dings';
	public ?int $num = 0;
}