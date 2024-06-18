<?php

namespace rocket\test;

use rocket\user\bo\RocketUser;
use rocket\core\model\Rocket;
use testmdl\relation\bo\IntegratedSrcTestObj;
use testmdl\relation\bo\IntegratedTargetTestObj;
use n2n\test\TestEnv;

class RocketTestEnv {

	static function setUpRocketUser(): RocketUser {
		$obj = new RocketUser();
		$obj->setNick('super');
		$obj->setPassword('pass-placeholder');
		$obj->setPower(RocketUser::POWER_SUPER_ADMIN);

		TestEnv::em()->persist($obj);

		return $obj;
	}
}