<?php

namespace testmdl\enum\bo;

use rocket\attribute\EiType;
use rocket\attribute\impl\EiPropEnum;

#[EiType]
class InvalidEnumTestObj {

	public int $id;


	#[EiPropEnum(['CTUSCH' => 'CTUSCH LABEL'])]
	public SomeBackedEnum $annotatedProp;


}
