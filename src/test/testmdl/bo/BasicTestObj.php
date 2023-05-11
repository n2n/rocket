<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;

#[EiType]
#[EiMenuItem('Holeradio', groupKey: 'super-duper', groupOrderIndex: 1, orderIndex: 1)]
class BasicTestObj {

	public int $id;
	public string $holeradio;

}