<?php

namespace rocket\impl\ei\component\prop\translation;

use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use PHPUnit\Framework\TestCase;
use testmdl\bo\TranslatableTestObj;
use testmdl\bo\TranslationTestObj;
use rocket\op\ei\component\prop\EiProp;

class TranslationEiPropNatureSetupTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testTypedEnums(): void {
		$spec = SpecTestEnv::setUpSpec([TranslatableTestObj::class, TranslationTestObj::class]);


		$eiType = $spec->getEiTypeByClassName(TranslatableTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(3, $eiProps);

		$this->assertInstanceOf(EiProp::class, $eiProps['translatableTestObjs']);
		$translationEiPropNature = $eiProps['translatableTestObjs']->getNature();
		$this->assertInstanceOf(TranslationEiPropNature::class, $translationEiPropNature);


		$eiType = $spec->getEiTypeByClassName(TranslationTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(3, $eiProps);
	}


}