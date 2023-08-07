<?php

namespace testmdl\bo\enum;

use rocket\attribute\EiType;
use rocket\attribute\impl\EiPropEnum;
use rocket\attribute\EiPreset;

#[EiType]
class InvalidEnumTestObj {

	public int $id;


	#[EiPropEnum(['CTUSCH' => 'CTUSCH LABEL'])]
	public SomeBackedEnum $annotatedProp;


}
