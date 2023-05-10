<?php

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiMenuItem;

#[EiType]
#[EiMenuItem('Holeradio3', groupKey: 'not-super-duper', groupName: 'Not Super Duper Gruper',
		transactionalEmEnabled: false, persistenceUnitName: 'holeradio-pu')]
class Basic3TestObj {

	public int $id;
	public string $holeradio3;

}