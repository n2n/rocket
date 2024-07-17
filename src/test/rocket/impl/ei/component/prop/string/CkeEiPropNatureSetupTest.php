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

namespace rocket\impl\ei\component\prop\string;

use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\enum\bo\EnumTestObj;
use PHPUnit\Framework\TestCase;
use testmdl\enum\bo\InvalidEnumTestObj;
use n2n\util\ex\err\ConfigurationError;
use rocket\impl\ei\component\prop\enum\EnumEiPropNature;
use testmdl\string\bo\CkeTestObj;
use rocket\impl\ei\component\prop\string\cke\CkeEiPropNature;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use testmdl\string\bo\CkeCssConfigMock;
use testmdl\string\bo\CkeLinkProviderMock;

class CkeEiPropNatureSetupTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testAnnotatedCke(): void {
		$spec = SpecTestEnv::setUpSpec([CkeTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(CkeTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(1, $eiProps);

		$ckeEiPropNature = $eiProps['ckeStr1']->getNature();
		assert($ckeEiPropNature instanceof CkeEiPropNature);
		$ckeConfig = $ckeEiPropNature->getCkeConfig();

		$this->assertInstanceOf(CkeCssConfigMock::class, $ckeConfig->getCssConfig());
		$this->assertCount(1, $ckeConfig->getLinkProviders());
		$this->assertInstanceOf(CkeLinkProviderMock::class, $ckeConfig->getLinkProviders()[0]);
	}
}