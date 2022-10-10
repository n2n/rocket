<?php

namespace rocket\ei\impl;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\PrimitiveReadPresetTestObj;
use testmdl\bo\ModTestObj;

class RocketEiComponentNatureProviderTest extends TestCase {

	function testPrimitiveReadPreset() {
		$spec = SpecTestEnv::setUpSpec([PrimitiveReadPresetTestObj::class]);


		$eiType = $spec->getEiTypeByClassName(PrimitiveReadPresetTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(4, $eiProps);
	}

	function testMod() {
		$spec = SpecTestEnv::setUpSpec([ModTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(ModTestObj::class);

		$eiMods = $eiType->getEiMask()->getEiModCollection()->toArray();

		$this->assertCount(2, $eiMods);
	}

}