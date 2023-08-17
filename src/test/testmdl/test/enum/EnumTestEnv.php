<?php

namespace testmdl\test\enum;

use n2n\test\TestEnv;
use testmdl\enum\bo\EnumTestObj;
use testmdl\enum\bo\SomeBackedEnum;

enum EnumTestEnv {

	static function setEnumTestObj(SomeBackedEnum $annotatedProp = SomeBackedEnum::ATUSCH): EnumTestObj {
		$obj = new EnumTestObj();
		$obj->annotatedProp = $annotatedProp;
		$obj->autoDetectedProp = SomeBackedEnum::BTUSCH;

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function findEnumTestObj(int $id): ?EnumTestObj {
		return TestEnv::em()->find(EnumTestObj::class, $id);
	}

}
