<?php

namespace testmdl\test\enum;

use n2n\test\TestEnv;
use testmdl\bo\enum\EnumTestObj;
use testmdl\bo\enum\SomeBackedEnum;

enum EnumTestEnv {

	static function setEnumTestObj(SomeBackedEnum $annotatedProp = SomeBackedEnum::ATUSCH): EnumTestObj {
		$obj = new EnumTestObj();
		$obj->annotatedProp = $annotatedProp;
		$obj->autoDetectedProp = SomeBackedEnum::BTUSCH;

		TestEnv::em()->persist($obj);

		return $obj;
	}

}
