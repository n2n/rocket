<?php

namespace rocket\impl\ei\component\prop\relation;

use PHPUnit\Framework\TestCase;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use n2n\test\TestEnv;
use testmdl\test\TestMdlTestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\si\api\SiGetInstruction;
use rocket\ui\si\meta\SiStyle;
use rocket\op\ei\manage\api\GetInstructionProcess;
use rocket\ui\si\api\SiPartialContentInstruction;
use testmdl\relation\bo\IntegratedSrcTestObj;
use testmdl\relation\bo\IntegratedTargetTestObj;
use n2n\util\uri\Url;
use rocket\op\spec\Spec;

class IntegratedOneToOneEiPropNatureTest extends TestCase {
	private Spec $spec;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([IntegratedSrcTestObj::class, IntegratedTargetTestObj::class]);

		$tx = TestEnv::createTransaction();
		TestMdlTestEnv::setUpIntegratedTestObj();
		TestMdlTestEnv::setUpIntegratedTestObj();
		$tx->commit();
	}

	function testBlabla() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(IntegratedSrcTestObj::class)->getEiMask();;

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->setBaseUrl(Url::create('/admin'));
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$siGetInstruction = new SiGetInstruction(new SiStyle(false, false));
		$siGetInstruction->setPartialContentInstruction(new SiPartialContentInstruction(0, 10));
		$process = new GetInstructionProcess($siGetInstruction, $eiFrame);

		$siGetResponse = $process->exec();

		$entries = $siGetResponse->getPartialContent()->getValueBoundaries();

		$this->assertCount(2, $entries);

	}
}