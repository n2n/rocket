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

namespace testmdl\test;

use n2n\test\TestEnv;
use testmdl\bo\SortTestObj;
use testmdl\bo\QuickSearchTestObj;
use testmdl\relation\bo\IntegratedSrcTestObj;
use testmdl\relation\bo\IntegratedTargetTestObj;
use testmdl\string\bo\StringTestObj;
use testmdl\bo\BasicTestObj;

class TestMdlTestEnv {
	static function setUpSortTestObj(?string $holeradio, ?int $num): SortTestObj {
		$obj = new SortTestObj();
		$obj->holeradio = $holeradio;
		$obj->num = $num;

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function setUpQuickSearchTestObj(string $holeradio, string $holeradio2): QuickSearchTestObj {
		$obj = new QuickSearchTestObj();
		$obj->holeradio = $holeradio;
		$obj->holeradio2 = $holeradio2;

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function setUpIntegratedTestObj(): IntegratedSrcTestObj {
		$obj = new IntegratedSrcTestObj();
		$obj->targetTestObj = new IntegratedTargetTestObj();
		$obj->targetTestObj->dingsel = 'hoi';

		TestEnv::em()->persist($obj);

		return $obj;
	}

	static function setUpStringTestObj(): StringTestObj {
		$obj = new StringTestObj();

		TestEnv::tem()->persist($obj);

		return $obj;
	}

	static function setUpBasicTestObj(string $holeradio = 'super holeradio'): BasicTestObj {
		$obj = new BasicTestObj();
		$obj->holeradio = $holeradio;

		TestEnv::tem()->persist($obj);

		return $obj;
	}

}