<?php

namespace testmdl\bo\enum;

use rocket\attribute\EiType;
use rocket\attribute\impl\EiPropEnum;
use rocket\attribute\EiPreset;

#[EiType]
#[EiPreset(editProps: ['autoDetectedProp'])]
class EnumTestObj {

	public int $id;

	public SomeBackedEnum $autoDetectedProp;

	#[EiPropEnum(['BTUSCH' => 'BTUSCH LABEL'])]
	public SomeBackedEnum $annotatedProp;


}
