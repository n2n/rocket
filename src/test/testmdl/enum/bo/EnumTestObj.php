<?php

namespace testmdl\enum\bo;

use rocket\attribute\EiType;
use rocket\attribute\impl\EiPropEnum;
use rocket\attribute\EiPreset;
use rocket\attribute\impl\EiCmdOverview;

#[EiType]
#[EiPreset(editProps: ['autoDetectedProp'])]
#[EiCmdOverview]
class EnumTestObj {

	public int $id;

	public SomeBackedEnum $autoDetectedProp;

	#[EiPropEnum(['BTUSCH' => 'BTUSCH LABEL'])]
	public SomeBackedEnum $annotatedProp;


}
