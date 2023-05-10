<?php

namespace rocket\op\ei\manage\critmod;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\SortTestObj;
use testmdl\test\TestMdlTestEnv;
use n2n\test\TestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;

class SortTest extends TestCase {
	private Spec $spec;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([SortTestObj::class]);

		$tx = TestEnv::createTransaction();
		TestMdlTestEnv::setUpSortTestObj('stusch2', 3);
		TestMdlTestEnv::setUpSortTestObj('stusch2', 2);
		TestMdlTestEnv::setUpSortTestObj('stusch1', 1);
		$tx->commit();
	}

	function testDefaultSort() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(SortTestObj::class)->getEiMask();;

		$sortSettings = $eiMask->getDef()->getDefaultSortSettingGroup()->getSortSettings();
		$this->assertCount(2, $sortSettings);

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$criteria = $eiFrame->createCriteria('sto')->select('sto');
		$sortTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(3, $sortTestObjs);

		$this->assertInstanceOf(SortTestObj::class, $sortTestObjs[0]);
		$this->assertEquals('stusch1', $sortTestObjs[0]->holeradio);
		$this->assertEquals(1, $sortTestObjs[0]->num);

		$this->assertInstanceOf(SortTestObj::class, $sortTestObjs[1]);
		$this->assertEquals('stusch2', $sortTestObjs[1]->holeradio);
		$this->assertEquals(3, $sortTestObjs[1]->num);

		$this->assertInstanceOf(SortTestObj::class, $sortTestObjs[2]);
		$this->assertEquals('stusch2', $sortTestObjs[2]->holeradio);
		$this->assertEquals(2, $sortTestObjs[2]->num);
	}
}