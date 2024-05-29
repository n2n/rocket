<?php

namespace testmdl\test\string;

use n2n\test\TestEnv;
use testmdl\string\bo\StringTestObj;

enum StringTestEnv {

	static function setUpStringTestObj(): StringTestObj {
		$obj = new StringTestObj();

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function findStringTestObj(int $id): ?StringTestObj {
		return TestEnv::em()->find(StringTestObj::class, $id);
	}

}
