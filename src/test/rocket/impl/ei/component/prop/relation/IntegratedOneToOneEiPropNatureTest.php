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

namespace rocket\impl\ei\component\prop\relation;

use PHPUnit\Framework\TestCase;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use n2n\test\TestEnv;
use testmdl\test\TestMdlTestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\ui\si\api\request\SiGetInstruction;
use rocket\op\ei\manage\api\GetInstructionProcess;
use rocket\ui\si\api\request\SiPartialContentInstruction;
use testmdl\relation\bo\IntegratedSrcTestObj;
use testmdl\relation\bo\IntegratedTargetTestObj;
use n2n\util\uri\Url;
use rocket\op\spec\Spec;
use rocket\op\ei\manage\gui\EiSiMaskId;
use rocket\ui\gui\ViewMode;
use rocket\ui\gui\api\GuiSiApiModel;
use rocket\op\ei\manage\gui\EiGuiApiModel;
use rocket\ui\si\api\SiApi;
use rocket\ui\si\api\request\SiApiCall;
use rocket\ui\si\api\request\SiGetRequest;
use rocket\ui\si\err\UnknownSiElementException;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\op\ei\UnknownEiTypeException;

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

	/**
	 * @throws UnknownSiElementException
	 * @throws CorruptedSiDataException
	 * @throws UnknownEiTypeException
	 */
	function testBlabla() {
		$eiLaunch = SpecTestEnv::setUpEiLaunch($this->spec);
		$eiMask = $this->spec->getEiTypeByClassName(IntegratedSrcTestObj::class)->getEiMask();;

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->setBaseUrl(Url::create('/admin'));
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$siGetInstruction = new SiGetInstruction(new EiSiMaskId($eiMask->getEiTypePath(), ViewMode::COMPACT_READ));
		$siGetInstruction->setPartialContentInstruction(new SiPartialContentInstruction(0, 10));
		$siGetRequest = new SiGetRequest();
		$siGetRequest->putInstruction('key', $siGetInstruction);
		$siApi = new SiApi(new GuiSiApiModel(new EiGuiApiModel($eiFrame)));

		$siApiCallResponse = $siApi->handleCall(new SiApiCall(getRequest: $siGetRequest), [], TestEnv::getN2nContext());
		$instructionResults = $siApiCallResponse->getGetResponse()->getInstructionResults();

		$this->assertNotNull($instructionResults['key']);
		$partialContent = $instructionResults['key']->getPartialContent();
		$this->assertCount(2, $partialContent->getValueBoundaries());
	}
}