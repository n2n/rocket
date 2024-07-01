<?php

namespace rocket\op\ei\manage\critmod;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\test\TestMdlTestEnv;
use n2n\test\TestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use testmdl\bo\QuickSearchTestObj;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\op\ei\EiPropPath;

class QuickSearchTest extends TestCase {
	private Spec $spec;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([QuickSearchTestObj::class]);

		$tx = TestEnv::createTransaction();
		TestMdlTestEnv::setUpQuickSearchTestObj('find me', 'random string');
		TestMdlTestEnv::setUpQuickSearchTestObj('find you', 'random string');
		$tx->commit();
	}

	function testQuickSearch() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(QuickSearchTestObj::class)->getEiMask();;

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiObjectSelector($eiFrame);

		$criteria = $eiFrameUtil->createCriteria('qsto', quickSearchStr: 'find me')->select('qsto');
		$quickSearchTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(1, $quickSearchTestObjs);

		$this->assertInstanceOf(QuickSearchTestObj::class, $quickSearchTestObjs[0]);
		$this->assertEquals('find me', $quickSearchTestObjs[0]->holeradio);

		$criteria = $eiFrameUtil->createCriteria('qsto', quickSearchStr: 'find')->select('qsto');
		$quickSearchTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(2, $quickSearchTestObjs);

		$this->assertInstanceOf(QuickSearchTestObj::class, $quickSearchTestObjs[0]);
		$this->assertEquals('find me', $quickSearchTestObjs[0]->holeradio);
		$this->assertInstanceOf(QuickSearchTestObj::class, $quickSearchTestObjs[1]);
		$this->assertEquals('find you', $quickSearchTestObjs[1]->holeradio);
	}


	function testQuickSearchDisabled() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(QuickSearchTestObj::class)->getEiMask();

		$eiMask->getEiPropCollection()->getByPath(EiPropPath::create('holeradio'))->getNature()->setQuickSearchable(false);

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiObjectSelector($eiFrame);


		$criteria = $eiFrameUtil->createCriteria('qsto', quickSearchStr: 'find')->select('qsto');
		$quickSearchTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(0, $quickSearchTestObjs);
	}
}