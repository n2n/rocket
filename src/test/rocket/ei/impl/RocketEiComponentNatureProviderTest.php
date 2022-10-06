<?php

namespace rocket\ei\impl;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\PrimitiveReadPresetTestObj;

class RocketEiComponentNatureProviderTest extends TestCase {

	function testPrimitiveReadPreset() {
		$spec = SpecTestEnv::setUpSpec([PrimitiveReadPresetTestObj::class]);


		$eiType = $spec->getEiTypeByClassName(PrimitiveReadPresetTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(4, $eiProps);

	}

}