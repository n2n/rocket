<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\MenuItem;

#[EiType]
#[MenuItem('Holeradio', groupKey: 'super-duper')]
class BasicTestObj {

	public int $id;
	public string $holeradio;

}