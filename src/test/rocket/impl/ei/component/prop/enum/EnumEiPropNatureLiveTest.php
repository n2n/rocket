<?php

namespace rocket\impl\ei\component\prop\enum;

use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\bo\QuickSearchTestObj;
use n2n\test\TestEnv;
use testmdl\test\TestMdlTestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\ei\manage\frame\EiFrameUtil;
use testmdl\bo\enum\EnumTestObj;
use testmdl\test\enum\EnumTestEnv;
use testmdl\bo\enum\SomeBackedEnum;
use PHPUnit\Framework\TestCase;
use rocket\op\ei\EiPropPath;

class EnumEiPropNatureLiveTest extends TestCase {


	private Spec $spec;

	private int $enumTestObj1Id;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([EnumTestObj::class]);

		$tx = TestEnv::createTransaction();
		$enumTestObj1 = EnumTestEnv::setEnumTestObj(SomeBackedEnum::ATUSCH);
		EnumTestEnv::setEnumTestObj(SomeBackedEnum::BTUSCH);
		$tx->commit();

		$this->enumTestObj1Id = $enumTestObj1->id;
	}

	function testQuickSearch() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(EnumTestObj::class)->getEiMask();

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiFrameUtil($eiFrame);

		$criteria = $eiFrameUtil->createCriteria('eto', quickSearchStr: 'ATUSCH')->select('eto');
		$enumTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(0, $enumTestObjs);
	}

	function testLoadEntry() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(EnumTestObj::class)->getEiMask();;

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiFrameUtil($eiFrame);

		$tx = TestEnv::createTransaction();
		$eiObject = $eiFrameUtil->lookupEiObject($this->enumTestObj1Id);
		$eiEntry = $eiFrame->createEiEntry($eiObject);
		$this->assertEquals(SomeBackedEnum::ATUSCH, $eiEntry->getValue(new EiPropPath(['annotatedProp'])));

		$eiEntry->setValue(new EiPropPath(['annotatedProp']), SomeBackedEnum::BTUSCH);
		$eiEntry->save();
		$tx->commit();

		$tx = TestEnv::createTransaction(true);
		$enumTestObj1 = EnumTestEnv::findEnumTestObj($this->enumTestObj1Id);
		$this->assertEquals(SomeBackedEnum::BTUSCH, $enumTestObj1->annotatedProp);
		$tx->commit();
	}
}
