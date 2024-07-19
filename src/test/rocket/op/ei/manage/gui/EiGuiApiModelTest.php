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

namespace rocket\op\ei\manage\gui;

use PHPUnit\Framework\TestCase;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\string\bo\StringTestObj;
use n2n\test\TestEnv;
use testmdl\test\string\StringTestEnv;
use testmdl\string\bo\StrObjMock;
use rocket\op\ei\UnknownEiTypeException;
use testmdl\bo\BasicTestObj;
use testmdl\test\TestMdlTestEnv;
use rocket\ui\si\api\SiApi;
use rocket\ui\gui\api\GuiSiApiModel;
use rocket\ui\si\api\request\SiApiCall;
use rocket\ui\si\api\request\SiValueBoundaryInput;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\ui\si\api\request\SiInput;
use rocket\ui\gui\ViewMode;
use rocket\ui\si\api\request\SiEntryInput;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\err\UnknownSiElementException;
use rocket\op\spec\Spec;
use rocket\ui\si\api\request\SiFieldInput;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\EiPropPath;
use rocket\ui\si\api\request\SiValRequest;
use rocket\ui\si\api\request\SiValGetInstruction;
use rocket\ui\si\api\request\SiValInstruction;
use rocket\op\ei\component\command\EiCmd;
use rocket\impl\ei\component\cmd\common\AddEiCmdNature;
use rocket\impl\ei\component\cmd\EiCmdNatures;
use rocket\ui\gui\control\GuiControl;
use rocket\impl\ei\manage\gui\GuiControls;
use rocket\ui\si\control\SiButton;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\ui\si\api\response\SiApiCallResponse;
use rocket\ui\si\api\request\SiControlCall;

class EiGuiApiModelTest extends TestCase {

	private Spec $spec;
	private int $basicTestObj1Id;
	private int $basicTestObj2Id;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([BasicTestObj::class]);

		$tx = TestEnv::createTransaction();
		$basicTestObj1 = TestMdlTestEnv::setUpBasicTestObj('huii1');
		$basicTestObj2 = TestMdlTestEnv::setUpBasicTestObj('huii2');
		$tx->commit();

		$this->basicTestObj1Id = $basicTestObj1->getId();
		$this->basicTestObj2Id = $basicTestObj2->getId();
	}

	/**
	 * @throws CorruptedSiDataException
	 * @throws UnknownSiElementException
	 * @throws UnknownEiTypeException
	 */
	function testNewInput(): void {
		$eiType = $this->spec->getEiTypeByClassName(BasicTestObj::class);
		$eiMask = $eiType->getEiMask();
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiMask);
		$maskId = (string) new EiSiMaskId($eiMask->getEiTypePath(), ViewMode::BULKY_ADD);

		$siEntryInput = new SiEntryInput(null);
		$siEntryInput->putFieldInput('holeradio', new SiFieldInput(['value' => 'new-value']));
		$siValueBoundaryInput = new SiValueBoundaryInput($maskId, $siEntryInput);

		$siInput = new SiInput();
		$siInput->putValueBoundaryInput('key', $siValueBoundaryInput);
		$siApiCall = new SiApiCall($siInput);

		$eiGuiApiModel = new EiGuiApiModel($eiFrame);
		$siApi = new SiApi(new GuiSiApiModel($eiGuiApiModel));
		$siApi->handleCall($siApiCall, [], TestEnv::getN2nContext());

		$map = $eiGuiApiModel->getCachedEiEntriesMap();
		$this->assertEquals(1, $map->count());

		$eiEntry = $map->getIterator()->current()[0];
		$this->assertInstanceOf(EiEntry::class, $eiEntry);
		$this->assertEquals('new-value', $eiEntry->getValue(new EiPropPath(['holeradio'])));
		$this->assertTrue($eiEntry->isNew());
	}

	/**
	 * @throws CorruptedSiDataException
	 * @throws UnknownSiElementException
	 * @throws UnknownEiTypeException
	 */
	function testUpdateInput(): void {
		$eiType = $this->spec->getEiTypeByClassName(BasicTestObj::class);
		$eiMask = $eiType->getEiMask();
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiMask);
		$maskId = (string) new EiSiMaskId($eiMask->getEiTypePath(), ViewMode::BULKY_EDIT);

		$siEntryInput = new SiEntryInput($this->basicTestObj2Id);
		$siEntryInput->putFieldInput('holeradio', new SiFieldInput(['value' => 'new-value']));
		$siValueBoundaryInput = new SiValueBoundaryInput($maskId, $siEntryInput);

		$siInput = new SiInput();
		$siInput->putValueBoundaryInput('key', $siValueBoundaryInput);
		$siApiCall = new SiApiCall($siInput);

		$eiGuiApiModel = new EiGuiApiModel($eiFrame);
		$siApi = new SiApi(new GuiSiApiModel($eiGuiApiModel));
		$result = $siApi->handleCall($siApiCall, [], TestEnv::getN2nContext());

		$this->assertNotNull($result->getInputResult());

		$map = $eiGuiApiModel->getCachedEiEntriesMap();
		$this->assertEquals(1, $map->count());

		$eiEntry = $map->getIterator()->current()[0];
		$this->assertInstanceOf(EiEntry::class, $eiEntry);
		$this->assertEquals('new-value', $eiEntry->getValue(new EiPropPath(['holeradio'])));
		$this->assertEquals($this->basicTestObj2Id, $eiEntry->getId());
	}

	/**
	 * @throws UnknownSiElementException
	 * @throws CorruptedSiDataException
	 * @throws UnknownEiTypeException
	 */
	function testCall(): void {
		$eiType = $this->spec->getEiTypeByClassName(BasicTestObj::class);
		$eiMask = $eiType->getEiMask();
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiMask);
		$maskId = (string) new EiSiMaskId($eiMask->getEiTypePath(), ViewMode::BULKY_EDIT);

		$called = false;
		$eiCmdNature = EiCmdNatures::callback()
				->addGeneralGuiControl(GuiControls::callback(SiButton::info('holeradio'), function () use (&$called) {
					$called = true;
				}));
		$eiMask->getEiCmdCollection()->add('some-cmd-id', $eiCmdNature);
		$siApiCall = new SiApiCall(controlCall: new SiControlCall($maskId, null, 'some-cmd-id.0'));

		$eiGuiApiModel = new EiGuiApiModel($eiFrame);
		$siApi = new SiApi(new GuiSiApiModel($eiGuiApiModel));
		$result = $siApi->handleCall($siApiCall, [], TestEnv::getN2nContext());

		$this->assertTrue($called);
	}

	/**
	 * @throws CorruptedSiDataException
	 * @throws UnknownSiElementException
	 * @throws UnknownEiTypeException
	 */
	function testCopy(): void {
		$eiType = $this->spec->getEiTypeByClassName(BasicTestObj::class);
		$eiMask = $eiType->getEiMask();
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiMask);
		$maskId = (string) new EiSiMaskId($eiMask->getEiTypePath(), ViewMode::BULKY_EDIT);


		$siEntryInput = new SiEntryInput($this->basicTestObj2Id);
		$siEntryInput->putFieldInput('holeradio', new SiFieldInput(['value' => 'new-value']));
		$siValueBoundaryInput = new SiValueBoundaryInput($maskId, $siEntryInput);

		$siValGetInstruction = new SiValInstruction($siValueBoundaryInput);
		$siValGetInstruction->putGetInstruction('copy', new SiValGetInstruction(new EiSiMaskId($eiMask->getEiTypePath(), ViewMode::COMPACT_READ)));

		$siValRequest = new SiValRequest();
		$siValRequest->putInstruction('key', $siValGetInstruction);

		$siApiCall = new SiApiCall(valRequest: $siValRequest);

		$eiGuiApiModel = new EiGuiApiModel($eiFrame);
		$siApi = new SiApi(new GuiSiApiModel($eiGuiApiModel));
		$result = $siApi->handleCall($siApiCall, [], TestEnv::getN2nContext());

		$this->assertNotNull($result->getValResponse());

		$map = $eiGuiApiModel->getCachedEiEntriesMap();
		$this->assertEquals(2, $map->count());

		$eiEntry = $map->getIterator()->current()[0];
		$this->assertInstanceOf(EiEntry::class, $eiEntry);
		$this->assertEquals('new-value', $eiEntry->getValue(new EiPropPath(['holeradio'])));
		$this->assertEquals($this->basicTestObj2Id, $eiEntry->getId());
	}
}