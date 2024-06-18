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
use rocket\ui\si\content\SiZoneCall;
use rocket\ui\si\input\SiInput;
use rocket\ui\si\input\SiEntryInput;
use rocket\ui\si\content\SiEntryIdentifier;
use rocket\op\ei\mask\EiMask;
use rocket\ui\gui\ViewMode;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\ui\si\input\SiFieldInput;
use testmdl\string\bo\StrObjMock;

class EditControllerTest extends TestCase {


	private Spec $spec;
	private EiMask $eiMask;
	private int $rocketUserId;
	private int $stringTestObjId;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([StringTestObj::class]);
		$this->eiMask = $this->spec->getEiTypeByClassName(StringTestObj::class)->getEiMask();
		$this->spec->addLaunchPad(new EiLaunchPad('launch-id', fn () => $this->eiMask));


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
	function testGet(): void {
		$result = TestEnv::http()->newRequest()->get('/admin/manage/launch-id/cmd/eecn-0/' . $this->stringTestObjId)
				->inject(function(Rocket $rocket, LoginContext $loginContext) {
					$rocket->setSpec($this->spec);
					$loginContext->loginByUserId($this->rocketUserId);
				})
				->exec();

		$jsonData = $result->parseJson();

		$this->assertEquals('bulky-entry', $jsonData['gui']['type']);

	}

	/**
	 * @throws StatusException
	 */
	function testHandleSaveCall(): void {

		$eiGuiMaskDeclaration = $this->eiMask->getEiEngine()
				->obtainEiGuiMaskDeclaration(ViewMode::BULKY_EDIT, null);

		$siInput = new SiInput();
		$siEntryIdentifier = new SiEntryIdentifier($eiGuiMaskDeclaration->createSiMaskIdentifier(), $this->stringTestObjId);
		$siEntryInput = new SiEntryInput($siEntryIdentifier);
		$siEntryInput->putFieldInput('annoHoleradio', new SiFieldInput(['value' => 'new-value']));
		$siEntryInput->putFieldInput('annoHoleradioObj', new SiFieldInput(['value' => 'nv']));
		$siInput->putEntryInput('0', $siEntryInput);


		$result = TestEnv::http()->newRequest()->post(
					'/admin/manage/launch-id/cmd/eecn-0/' . $this->stringTestObjId,
							['si-zone-call' => json_encode(new SiZoneCall($siInput, EditController::CONTROL_SAVE_KEY))])
				->inject(function(Rocket $rocket, LoginContext $loginContext) {
					$rocket->setSpec($this->spec);
					$loginContext->loginByUserId($this->rocketUserId);
				})
				->exec();

		$this->assertNull($result->parseJson()['inputError']);

		$tx = TestEnv::createTransaction(true);
		$this->assertEquals('new-value', TestMdlTestEnv::findStringTestObj($this->stringTestObjId)->annoHoleradio);
		$this->assertEquals(new StrObjMock('nv'), TestMdlTestEnv::findStringTestObj($this->stringTestObjId)->annoHoleradioObj);
		$tx->commit();
	}

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