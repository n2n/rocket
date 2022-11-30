<?php

namespace rocket\ei\manage\critmod;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\SortTestObj;
use testmdl\test\TestMdlTestEnv;
use n2n\test\TestEnv;
use rocket\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\spec\Spec;
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

	function testSort() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiEngine = $this->spec->getEiTypeByClassName(SortTestObj::class)->getEiMask()->getEiEngine();

		$eiFrame = $eiLaunch->createRootEiFrame($eiEngine);

		$criteria = $eiFrame->createCriteria('sto');
		$sortTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(3, $sortTestObjs);

		$this->assertEquals('stusch1', $sortTestObjs[0]->holeradio);
		$this->assertEquals(1, $sortTestObjs[0]->num);

		$this->assertEquals('stusch2', $sortTestObjs[1]->holeradio);
		$this->assertEquals(3, $sortTestObjs[1]->num);

		$this->assertEquals('stusch2', $sortTestObjs[2]->holeradio);
		$this->assertEquals(2, $sortTestObjs[2]->num);
	}
}