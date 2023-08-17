<?php

namespace rocket\impl\ei\component\prop\embedded;

use PHPUnit\Framework\TestCase;
use n2n\test\TestEnv;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\enum\bo\EnumTestObj;
use rocket\impl\ei\component\prop\enum\EnumEiPropNature;
use testmdl\embedded\bo\EmbeddingContainerTestObj;
use rocket\impl\ei\component\prop\string\StringEiPropNature;

class EmbeddedEiPropNatureSetupTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testPreset(): void {
		$spec = SpecTestEnv::setUpSpec([EmbeddingContainerTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(EmbeddingContainerTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(5, $eiProps);

		$embeddedEiPropNature = $eiProps['optEditEmbeddable']->getNature();
		$this->assertInstanceOf(EmbeddedEiPropNature::class, $embeddedEiPropNature);

		$stringEiPropNature = $eiProps['optEditEmbeddable.someProp']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $stringEiPropNature);
	}

}