<?php

namespace rocket\impl\ei\component\prop\enum;

use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\bo\enum\EnumTestObj;
use PHPUnit\Framework\TestCase;
use rocket\op\ei\EiPropPath;
use testmdl\bo\enum\InvalidEnumTestObj;
use n2n\util\ex\err\ConfigurationError;

class EnumEiPropNatureSetupTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testTypedEnums(): void {
		$spec = SpecTestEnv::setUpSpec([EnumTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(EnumTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(2, $eiProps);

		$enumEiPropNature = $eiProps['autoDetectedProp']->getNature();
		$this->assertInstanceOf(EnumEiPropNature::class, $enumEiPropNature);
		$this->assertNotNull($enumEiPropNature->getEnum());
		$this->assertEquals(['ATUSCH' => 'ATUSCH', 'BTUSCH' => 'BTUSCH'], $enumEiPropNature->getOptions());

		$enumEiPropNature = $eiProps['annotatedProp']->getNature();
		$this->assertInstanceOf(EnumEiPropNature::class, $enumEiPropNature);
		$this->assertNotNull($enumEiPropNature->getEnum());
		$this->assertEquals(['BTUSCH' => 'BTUSCH LABEL'], $enumEiPropNature->getOptions());
	}


	function testInvalidAnnotated(): void {
		$spec = SpecTestEnv::setUpSpec([InvalidEnumTestObj::class]);


		$eiType = $spec->getEiTypeByClassName(InvalidEnumTestObj::class);

		$this->expectException(ConfigurationError::class);
		$eiType->getEiMask()->getEiPropCollection()->toArray();

	}
}