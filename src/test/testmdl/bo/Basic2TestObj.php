<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;

#[EiType]
#[EiMenuItem('Holeradio2', groupName: 'Super Duper Guper', groupKey: 'super-duper', groupOrderIndex: 3, orderIndex: 2)]
class Basic2TestObj {

	public int $id;
	public string $holeradio2;

}