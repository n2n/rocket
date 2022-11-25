<?php

namespace rocket\impl\ei\component\prop\string;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\PrimitiveReadPresetTestObj;
use testmdl\string\bo\StringTestObj;
use rocket\ei\util\Eiu;
use n2n\test\TestEnv;

class StringEiPropNatureTest extends TestCase {


	function setUp(): void {

	}

	function testQuickSearch() {
		$spec = SpecTestEnv::setUpSpec([StringTestObj::class]);


		$eiu = new Eiu($spec, TestEnv::getN2nContext());

		$this->assertTrue(true);

	}


}