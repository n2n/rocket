<?php

namespace rocket\op\ei\manage\api;

use PHPUnit\Framework\TestCase;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\string\bo\StringTestObj;
use rocket\op\ei\EiLaunchPad;
use n2n\test\TestEnv;
use rocket\test\RocketTestEnv;
use testmdl\test\TestMdlTestEnv;
use n2n\web\http\StatusException;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use rocket\op\ei\UnknownEiTypeException;
use n2n\util\uri\Url;
use rocket\ui\si\api\request\SiApiCall;
use rocket\ui\si\api\request\SiGetRequest;
use rocket\ui\si\api\request\SiGetInstruction;
use rocket\op\ei\manage\gui\EiSiMaskId;
use rocket\ui\gui\ViewMode;
use rocket\op\spec\Spec;
use rocket\op\ei\mask\EiMask;

class ApiControllerTest extends TestCase {

	private Spec $spec;
	private EiMask $eiMask;
	private int $rocketUserId;
	private int $stringTestObjId;

	/**
	 * @throws UnknownEiTypeException
	 */
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
		$siGetInstruction = new SiGetInstruction(new EiSiMaskId($this->eiMask->getEiTypePath(), ViewMode::BULKY_READ));
		$siGetInstruction->setEntryId($this->stringTestObjId);
		$siGetRequest = new SiGetRequest();
		$siGetRequest->putInstruction('key', $siGetInstruction);
		$siApiCall = new SiApiCall(getRequest: $siGetRequest);

		$result = TestEnv::http()->newRequest()
				->post(Url::create('/admin/manage/launch-id/api/eecn-0'), ['call' => json_encode($siApiCall)])
				->inject(function(Rocket $rocket, LoginContext $loginContext) {
					$rocket->setSpec($this->spec);
					$loginContext->loginByUserId($this->rocketUserId);
				})
				->exec();

		$jsonData = $result->parseJson();

		$this->assertNotNull('bulky-entry', $jsonData['getResponse']);
	}
}