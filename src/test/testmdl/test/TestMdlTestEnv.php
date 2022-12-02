<?php

namespace testmdl\test;

use n2n\test\TestEnv;
use testmdl\bo\SortTestObj;
use testmdl\bo\QuickSearchTestObj;

class TestMdlTestEnv {
	static function setUpSortTestObj(?string $holeradio, ?int $num): SortTestObj {
		$obj = new SortTestObj();
		$obj->holeradio = $holeradio;
		$obj->num = $num;

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function setUpQuickSearchTestObj(string $holeradio, string $holeradio2): QuickSearchTestObj {
		$obj = new QuickSearchTestObj();
		$obj->holeradio = $holeradio;
		$obj->holeradio2 = $holeradio2;

		TestEnv::em()->persist($obj);

		return $obj;
	}
}