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

use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\enum\bo\EnumTestObj;
use PHPUnit\Framework\TestCase;
use testmdl\enum\bo\InvalidEnumTestObj;
use n2n\util\ex\err\ConfigurationError;

class EnumEiPropNatureSetupTest extends TestCase {

	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testTypedEnums(): void {
		$spec = SpecTestEnv::setUpSpec([EnumTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(EnumTestObj::class);
		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();

		$this->assertCount(2, $eiProps);

		$enumEiPropNature = $eiProps['autoDetectedProp']->getNature();
		$this->assertInstanceOf(EnumEiPropNature::class, $enumEiPropNature);
		$this->assertNotNull($enumEiPropNature->getEnum());
		$this->assertEquals(['ATUSCH' => 'ATUSCH', 'BTUSCH' => 'BTUSCH'], $enumEiPropNature->getOptions());

		$enumEiPropNature = $eiProps['annotatedProp']->getNature();
		$this->assertInstanceOf(EnumEiPropNature::class, $enumEiPropNature);
		$this->assertNotNull($enumEiPropNature->getEnum());
		$this->assertEquals(['BTUSCH' => 'BTUSCH LABEL'], $enumEiPropNature->getOptions());
	}


	function testInvalidAnnotated(): void {
		$spec = SpecTestEnv::setUpSpec([InvalidEnumTestObj::class]);

		$eiType = $spec->getEiTypeByClassName(InvalidEnumTestObj::class);

		$this->expectException(ConfigurationError::class);
		$eiType->getEiMask()->getEiPropCollection()->toArray();
	}
}