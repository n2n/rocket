<?php

namespace rocket\op\ei\impl;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\PrimitiveReadPresetTestObj;
use testmdl\bo\ModTestObj;
use testmdl\bo\RelationTestObj1;
use testmdl\bo\RelationTestObj2;
use testmdl\bo\TranslatableTestObj;
use testmdl\bo\TranslationTestObj;
use rocket\impl\ei\component\prop\translation\TranslationEiPropNature;
use rocket\impl\ei\component\prop\bool\BooleanEiPropNature;
use testmdl\bo\AnnotatedReadPresetTestObj;
use rocket\test\GeneralTestEnv;
use testmdl\bo\CiContainerTestObj;
use rocket\impl\ei\component\prop\ci\ContentItemsEiPropNature;
use rocket\impl\ei\component\prop\ci\model\ContentItem;

class RocketEiComponentNatureProviderTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testPrimitiveReadPreset() {
		$spec = SpecTestEnv::setUpSpec([PrimitiveReadPresetTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(PrimitiveReadPresetTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(7, $eiProps);

		$this->assertTrue($eiProps['id']->getNature()->isReadOnly());
		$this->assertFalse($eiProps['id']->getNature()->isMandatory());
		$this->assertEquals('Id', $eiProps['id']->getNature()->getLabelConfig()->getLabel());

		$this->assertTrue($eiProps['stringPriTest']->getNature()->isReadOnly());
		$this->assertTrue($eiProps['stringPriTest']->getNature()->isMandatory());
		$this->assertEquals('Test Label', $eiProps['stringPriTest']->getNature()->getLabelConfig()->getLabel());
		$this->assertEquals('Test Help Text', $eiProps['stringPriTest']->getNature()->getLabelConfig()->getHelpText());

		$this->assertTrue($eiProps['stringNullPriTest']->getNature()->isReadOnly());
		$this->assertFalse($eiProps['stringNullPriTest']->getNature()->isMandatory());

		$this->assertFalse($eiProps['stringEditablePriTest']->getNature()->isReadOnly());
		$this->assertTrue($eiProps['stringEditablePriTest']->getNature()->isMandatory());
		$this->assertEquals('Super duper label', $eiProps['stringEditablePriTest']->getNature()->getLabelConfig()->getLabel());

		$this->assertFalse($eiProps['stringEditableNullNotNullTest']->getNature()->isReadOnly());
		$this->assertTrue($eiProps['stringEditableNullNotNullTest']->getNature()->isMandatory());

		$this->assertInstanceOf(BooleanEiPropNature::class, $eiProps['boolPubTest']->getNature());


		$this->assertTrue($eiProps['stringGetTest']->getNature()->isReadOnly());
		$this->assertTrue($eiProps['stringGetTest']->getNature()->isMandatory());
	}

	function testAnnotated() {
		$spec = SpecTestEnv::setUpSpec([AnnotatedReadPresetTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(AnnotatedReadPresetTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(5, $eiProps);

		$this->assertEquals(['id', 'pubBoolTest', 'pubEnumTest', 'pubDecimalTest', 'pubStringTest'], array_keys($eiProps));


		$this->assertTrue($eiProps['pubStringTest']->getNature()->isMultiline());
	}

	function testMod() {
		$spec = SpecTestEnv::setUpSpec([ModTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(ModTestObj::class);

		$eiMods = $eiType->getEiMask()->getEiModCollection()->toArray();

		$this->assertCount(2, $eiMods);
	}

	function testRelationProps() {
		$spec = SpecTestEnv::setUpSpec([RelationTestObj1::class, RelationTestObj2::class]);

		$eiType = $spec->getEiTypeByClassName(RelationTestObj1::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(2, $eiProps);
	}

	function testTranslations() {
		$spec = SpecTestEnv::setUpSpec([TranslatableTestObj::class, TranslationTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(TranslatableTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(1, $eiProps);

		$nature = $eiProps['translatableTestObjs']->getNature();
		$this->assertInstanceOf(TranslationEiPropNature::class, $nature);
	}

	function testContentItems() {
		$spec = SpecTestEnv::setUpSpec([CiContainerTestObj::class, ContentItem::class]);

		$eiType = $spec->getEiTypeByClassName(CiContainerTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(2, $eiProps);

		$nature = $eiProps['contentItems']->getNature();
		$this->assertInstanceOf(ContentItemsEiPropNature::class, $nature);
	}
}
