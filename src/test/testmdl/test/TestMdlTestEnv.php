<?php

namespace testmdl\test;

use n2n\test\TestEnv;
use testmdl\bo\SortTestObj;

class TestMdlTestEnv {
	static function setUpSortTestObj(?string $holeradio, ?int $num): SortTestObj {
		$obj = new SortTestObj();
		$obj->holeradio = $holeradio;
		$obj->num = $num;

		TestEnv::em()->persist($obj);

		return $obj;
	}
}