<?php

namespace testmdl\test;

use em\admin\org\bo\Organisation;
use em\admin\org\bo\PublicOrgFile;
use em\admin\org\bo\PrivateOrgFile;
use n2n\test\TestEnv;
use testmdl\bo\SortTestObj;

class TestMdlTestEnv {
	static function setUpSortTestObj(?string $holeradio): SortTestObj {
		$obj = new SortTestObj();
		$obj->holeradio = $holeradio;

		TestEnv::em()->persist($obj);

		return $obj;
	}
}