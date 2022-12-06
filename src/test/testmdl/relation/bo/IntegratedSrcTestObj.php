<?php

namespace testmdl\relation\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiDefaultSort;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiPropOneToOneIntegrated;
use n2n\persistence\orm\attribute\ManyToOne;
use n2n\persistence\orm\CascadeType;

#[EiType]
#[EiPreset(EiPresetMode::READ)]
class IntegratedSrcTestObj {
	public $id;
	public string $holeradio = 'str';
	#[EiPropOneToOneIntegrated]
	#[ManyToOne(cascade: CascadeType::ALL)]
	public IntegratedTargetTestObj $targetTestObj;
}