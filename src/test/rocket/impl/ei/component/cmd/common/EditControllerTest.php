<?php

namespace rocket\impl\ei\component\cmd\common;

use rocket\op\ei\manage\EiLaunch;
use n2n\test\TestEnv;
use rocket\user\model\security\FullEiPermissionManager;
use testmdl\relation\bo\IntegratedSrcTestObj;
use n2n\util\uri\Url;
use rocket\ui\si\api\SiPartialContentInstruction;
use rocket\op\ei\manage\api\GetInstructionProcess;
use testmdl\string\bo\StringTestObj;
use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\relation\bo\IntegratedTargetTestObj;
use testmdl\test\TestMdlTestEnv;
use rocket\impl\ei\component\cmd\common\controller\EditController;
use rocket\op\ei\EiLaunchPad;
use rocket\op\ei\manage\frame\EiFrameController;
use n2n\web\http\controller\ControllerContext;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use rocket\test\RocketTestEnv;
use PHPUnit\Framework\TestCase;
use n2n\web\http\StatusException;
use rocket\op\ei\util\entry\EiuObject;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\manage\LiveEiObject;
use rocket\op\ei\manage\EiEntityObj;

class EditControllerTest extends TestCase {


	private Spec $spec;
	private int $rocketUserId;
	private int $stringTestObjId;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([StringTestObj::class]);
		$eiMask = $this->spec->getEiTypeByClassName(StringTestObj::class)->getEiMask();
		$this->spec->addLaunchPad(new EiLaunchPad('launch-id', fn () => $eiMask));


		$tx = TestEnv::createTransaction();
		$rocketUser = RocketTestEnv::setUpRocketUser();
		$stringTestObj = TestMdlTestEnv::setUpStringTestObj();

		$tx->commit();

		$this->rocketUserId = $rocketUser->getId();
		$this->stringTestObjId = $stringTestObj->id;
	}

	/**
	 * @throws StatusException
	 */
	function testHoleradio(): void {
		$result = TestEnv::http()->newRequest()->get('/admin/manage/launch-id/cmd/eecn-0/' . $this->stringTestObjId)
				->inject(function (Rocket $rocket, LoginContext $loginContext) {
					$rocket->setSpec($this->spec);
					$loginContext->loginByUserId($this->rocketUserId);
				})
				->exec();

//
//		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
//		$eiMask = $this->spec->getEiTypeByClassName(StringTestObj::class)->getEiMask();
//
//		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
//		$eiFrame->setBaseUrl(Url::create('/admin'));
//
//
//		$eiLaunchPad = new EiLaunchPad('some-id', fn () => $eiMask);
//		$controller = $eiLaunchPad->lookupController(TestEnv::getN2nContext());
//		$controllerContext = new ControllerContext(new Path(['edit']), new Path(['admin']), $controller);
//		$controllerContext->execute();
//
//		$editController = new EditController();
//		$entries = $siGetResponse->getPartialContent()->getValueBoundaries();
	}
}