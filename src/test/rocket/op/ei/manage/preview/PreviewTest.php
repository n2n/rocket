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

namespace rocket\op\ei\manage\preview;

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\SortTestObj;
use testmdl\test\TestMdlTestEnv;
use n2n\test\TestEnv;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use testmdl\bo\PreviewTestObj;
use rocket\op\ei\UnknownEiTypeException;

class PreviewTest extends TestCase {
	private Spec $spec;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([PreviewTestObj::class]);
	}

	/**
	 * @throws UnknownEiTypeException
	 */
	function testSpec() {
		$this->assertEquals('tbd', $this->spec->getEiTypeByClassName(PreviewTestObj::class)
				->getEiMask()->getDef()->getPreviewControllerLookupId());
	}
}