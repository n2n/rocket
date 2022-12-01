<?php
namespace rocket\test;

use n2n\test\TestEnv;
use em\pgc\facade\PgcRestrictions;

class GeneralTestEnv  {
	
	static function teardown() {

		if (TestEnv::container()->tm()->hasOpenTransaction()) {
			TestEnv::container()->tm()->getRootTransaction()->rollBack();
		}

	    TestEnv::em()->clear();

		\n2n\test\TestEnv::db()->truncate();
//		\n2n\test\TestEnv::db()->pdo()->exec('DELETE FROM sqlite_sequence');

		TestEnv::getN2nContext()->clearLookupInjections();
		TestEnv::replaceN2nContext();
	}
}
