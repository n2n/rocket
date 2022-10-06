<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\MenuItem;

#[EiType]
#[MenuItem('Holeradio2', groupName: 'Super Duper Guper', groupKey: 'super-duper')]
class Basic2TestObj {

	public int $id;
	public string $holeradio2;

}