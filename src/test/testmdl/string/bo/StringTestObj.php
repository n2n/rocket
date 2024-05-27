<?php

namespace testmdl\string\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;

#[EiType]
class StringTestObj {

	private int $id;
	public string $holeradio;

	public StringValueObjectMock $holeradioObj;


}