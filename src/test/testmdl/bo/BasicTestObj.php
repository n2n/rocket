<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;

#[EiType]
#[EiMenuItem('Holeradio', groupKey: 'super-duper')]
class BasicTestObj {

	public int $id;
	public string $holeradio;

}