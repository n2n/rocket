<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\MenuItem;

#[EiType]
#[MenuItem('Holeradio3', groupKey: 'not-super-duper', groupName: 'Not Super Duper Gruper')]
class Basic3TestObj {

	public int $id;
	public string $holeradio3;

}