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
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(TranslatableTestObj::class)->getEiMask();

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiFrameUtil($eiFrame);

		$eiObject = $eiFrameUtil->lookupEiObject($this->translatableTestObj1Id);

		$is = $eiMask->getEiEngine()->getIdNameDefinition()->createIdentityStringFromPattern(
				'hui: {translatableTestObjs.name}', $eiFrame->getN2nContext(), $eiObject,
				N2nLocale::getDefault());

		$this->assertEquals('hui: holeradio', $is);
	}

}
