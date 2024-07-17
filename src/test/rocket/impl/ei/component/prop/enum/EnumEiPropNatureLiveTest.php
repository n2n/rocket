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

namespace rocket\impl\ei\component\prop\enum;

use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use n2n\test\TestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\ei\manage\frame\EiObjectSelector;
use testmdl\enum\bo\EnumTestObj;
use testmdl\test\enum\EnumTestEnv;
use testmdl\enum\bo\SomeBackedEnum;
use PHPUnit\Framework\TestCase;
use rocket\op\ei\EiPropPath;

class EnumEiPropNatureLiveTest extends TestCase {


	private Spec $spec;

	private int $enumTestObj1Id;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([EnumTestObj::class]);

		$tx = TestEnv::createTransaction();
		$enumTestObj1 = EnumTestEnv::setEnumTestObj(SomeBackedEnum::ATUSCH);
		EnumTestEnv::setEnumTestObj(SomeBackedEnum::BTUSCH);
		$tx->commit();

		$this->enumTestObj1Id = $enumTestObj1->id;
	}

	function testQuickSearch() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(EnumTestObj::class)->getEiMask();

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiObjectSelector($eiFrame);

		$criteria = $eiFrameUtil->createCriteria('eto', quickSearchStr: 'ATUSCH')->select('eto');
		$enumTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(0, $enumTestObjs);
	}

	function testLoadEntry() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(EnumTestObj::class)->getEiMask();;

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$eiFrameUtil = new EiObjectSelector($eiFrame);

		$tx = TestEnv::createTransaction();
		$eiObject = $eiFrameUtil->lookupEiObject($this->enumTestObj1Id);
		$eiEntry = $eiFrame->createEiEntry($eiObject);
		$this->assertEquals(SomeBackedEnum::ATUSCH, $eiEntry->getValue(new EiPropPath(['annotatedProp'])));

		$eiEntry->setValue(new EiPropPath(['annotatedProp']), SomeBackedEnum::BTUSCH);
		$eiEntry->save();
		$tx->commit();

		$tx = TestEnv::createTransaction(true);
		$enumTestObj1 = EnumTestEnv::findEnumTestObj($this->enumTestObj1Id);
		$this->assertEquals(SomeBackedEnum::BTUSCH, $enumTestObj1->annotatedProp);
		$tx->commit();
	}
}
