<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */

namespace rocket\impl\ei\component\cmd\common;

use n2n\test\TestEnv;
use rocket\ui\si\api\request\SiPartialContentInstruction;
use testmdl\string\bo\StringTestObj;
use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\test\TestMdlTestEnv;
use rocket\impl\ei\component\cmd\common\controller\EditController;
use rocket\op\ei\EiLaunchPad;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use rocket\test\RocketTestEnv;
use PHPUnit\Framework\TestCase;
use n2n\web\http\StatusException;
use rocket\ui\si\api\request\SiZoneCall;
use rocket\ui\si\api\request\SiInput;
use rocket\ui\si\api\request\SiEntryInput;
use rocket\op\ei\mask\EiMask;
use rocket\ui\gui\ViewMode;
use rocket\ui\si\api\request\SiFieldInput;
use testmdl\string\bo\StrObjMock;
use rocket\ui\si\api\request\SiValueBoundaryInput;
use rocket\op\ei\UnknownEiTypeException;
use testmdl\test\string\StringTestEnv;
use rocket\impl\ei\component\cmd\common\controller\AddController;

class AddControllerTest extends TestCase {

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
		$result = TestEnv::http()->newRequest()->get('/admin/manage/launch-id/cmd/aecn-0/' . $this->stringTestObjId)
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

		$eiGuiDefinition = $this->eiMask->getEiEngine()->getEiGuiDefinition(ViewMode::BULKY_ADD);

		$siInput = new SiInput();
		$siEntryInput = new SiEntryInput($this->stringTestObjId);
		$siEntryInput->putFieldInput('holeradio', new SiFieldInput(['value' => 'new-value']));
		$siEntryInput->putFieldInput('holeradioObj', new SiFieldInput(['value' => 'nv']));
		$siValueBoundaryInput = new SiValueBoundaryInput($eiGuiDefinition->createSiMaskIdentifier()->getId(), $siEntryInput);

		$siInput->putValueBoundaryInput('0', $siValueBoundaryInput);

		$result = TestEnv::http()->newRequest()->post(
					'/admin/manage/launch-id/cmd/aecn-0/' . $this->stringTestObjId,
					['si-zone-call' => json_encode(new SiZoneCall($siInput, AddController::CONTROL_SAVE_KEY))])
				->inject(function(Rocket $rocket, LoginContext $loginContext) {
					$rocket->setSpec($this->spec);
					$loginContext->loginByUserId($this->rocketUserId);
				})
				->exec();

		$resultData = $result->parseJson();

		$this->assertNotNull($resultData['inputResult']);
		$this->assertNotNull($resultData['callResult']);

		$tx = TestEnv::createTransaction(true);
		$this->assertEquals('new-value', StringTestEnv::findStringTestObj($this->stringTestObjId)->holeradio);
		$this->assertEquals(new StrObjMock('nv'), StringTestEnv::findStringTestObj($this->stringTestObjId)->holeradioObj);
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