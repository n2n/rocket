<?php

namespace rocket\impl\ei\component\prop\translation;

use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use n2n\test\TestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\ei\manage\frame\EiFrameUtil;
use testmdl\enum\bo\EnumTestObj;
use testmdl\test\enum\EnumTestEnv;
use testmdl\enum\bo\SomeBackedEnum;
use PHPUnit\Framework\TestCase;
use rocket\op\ei\EiPropPath;
use testmdl\test\translation\TranslationTestEnv;
use testmdl\bo\TranslationTestObj;
use testmdl\bo\TranslatableTestObj;
use n2n\l10n\N2nLocale;
use rocket\op\ei\manage\LiveEiObject;

class TranslationEiPropNatureLiveTest extends TestCase {


	private Spec $spec;

	private int $translatableTestObj1Id;
//	private int $translationTestObj1Id;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([TranslatableTestObj::class, TranslationTestObj::class]);

		$tx = TestEnv::createTransaction();
		$translatableTestObj = TranslationTestEnv::setUpTranslatableTestObj();
		$translationTestObj = TranslationTestEnv::setUpTranslationTestObj($translatableTestObj, 'de_CH', 'holeradio');
		$tx->commit();

		$this->translatableTestObj1Id = $translatableTestObj->getId();
//		$this->translationTestObj1Id = $translationTestObj->id;
	}

	function testIdentityString() {
		$eiType = $this->spec->getEiTypeByClassName(TranslatableTestObj::class);

		$eiObject = $eiType->createEiObject(TranslationTestEnv::findTranslatableTestObj($this->translatableTestObj1Id));

		$is = $eiType->getEiMask()->getEiEngine()->getIdNameDefinition()->createIdentityStringFromPattern(
				'hui: {translatableTestObjs.name}', TestEnv::getN2nContext(), $eiObject,
				N2nLocale::getDefault());

		$this->assertEquals('hui: holeradio', $is);
	}

}
