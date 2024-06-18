<?php

namespace testmdl\test;

use n2n\test\TestEnv;
use testmdl\bo\SortTestObj;
use testmdl\bo\QuickSearchTestObj;
use testmdl\relation\bo\IntegratedSrcTestObj;
use testmdl\relation\bo\IntegratedTargetTestObj;
use testmdl\string\bo\StringTestObj;

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

	static function setUpIntegratedTestObj(): IntegratedSrcTestObj {
		$obj = new IntegratedSrcTestObj();
		$obj->targetTestObj = new IntegratedTargetTestObj();
		$obj->targetTestObj->dingsel = 'hoi';

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function setUpStringTestObj(): StringTestObj {
		$obj = new StringTestObj();

		TestEnv::tem()->persist($obj);

		return $obj;
	}

	static function findStringTestObj(int $id): ?StringTestObj {
		return TestEnv::tem()->find(StringTestObj::class, $id);
	}
}