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

namespace rocket\op\ei\manage\critmod;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\SortTestObj;
use testmdl\test\TestMdlTestEnv;
use n2n\test\TestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\spec\Spec;
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

	function testDefaultSort() {
		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
		$eiMask = $this->spec->getEiTypeByClassName(SortTestObj::class)->getEiMask();;

		$sortSettings = $eiMask->getDef()->getDefaultSortSettingGroup()->getSortSettings();
		$this->assertCount(2, $sortSettings);

		$eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());

		$criteria = $eiFrame->createCriteria('sto')->select('sto');
		$sortTestObjs = $criteria->toQuery()->fetchArray();

		$this->assertCount(3, $sortTestObjs);

		$this->assertInstanceOf(SortTestObj::class, $sortTestObjs[0]);
		$this->assertEquals('stusch1', $sortTestObjs[0]->holeradio);
		$this->assertEquals(1, $sortTestObjs[0]->num);

		$this->assertInstanceOf(SortTestObj::class, $sortTestObjs[1]);
		$this->assertEquals('stusch2', $sortTestObjs[1]->holeradio);
		$this->assertEquals(3, $sortTestObjs[1]->num);

		$this->assertInstanceOf(SortTestObj::class, $sortTestObjs[2]);
		$this->assertEquals('stusch2', $sortTestObjs[2]->holeradio);
		$this->assertEquals(2, $sortTestObjs[2]->num);
	}
}