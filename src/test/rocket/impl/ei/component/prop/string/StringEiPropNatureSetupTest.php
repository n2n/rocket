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

use PHPUnit\Framework\TestCase;
use rocket\test\SpecTestEnv;
use testmdl\bo\PrimitiveReadPresetTestObj;
use testmdl\string\bo\StringTestObj;
use rocket\op\ei\util\Eiu;
use n2n\test\TestEnv;
use rocket\test\GeneralTestEnv;
use rocket\op\ei\component\prop\EiProp;
use n2n\spec\valobj\scalar\StringValueObject;
use testmdl\string\bo\StrObjMock;

class StringEiPropNatureSetupTest extends TestCase {


	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	function testSetup(): void {
		$spec = SpecTestEnv::setUpSpec([StringTestObj::class]);
		$eiType = $spec->getEiTypeByClassName(StringTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();
		$this->assertCount(6, $eiProps);

		$nature = $eiProps['holeradio']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $nature);
		$this->assertFalse($nature->isMultiline());
		$this->assertFalse($nature->isConstant());
		$this->assertFalse($nature->isReadOnly());
		$this->assertFalse($nature->isMandatory());
		$this->assertNull($nature->getStringValueObjectTypeName());

		$nature = $eiProps['mandatoryHoleradio']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $nature);
		$this->assertFalse($nature->isMultiline());
		$this->assertFalse($nature->isConstant());
		$this->assertFalse($nature->isReadOnly());
		$this->assertTrue($nature->isMandatory());
		$this->assertNull($nature->getStringValueObjectTypeName());

		$nature = $eiProps['annoHoleradio']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $nature);
		$this->assertTrue($nature->isMultiline());
		$this->assertSame(2, $nature->getMinlength());
		$this->assertSame(512, $nature->getMaxlength());
		$this->assertTrue($nature->isConstant());
		$this->assertTrue($nature->isReadOnly());
		$this->assertTrue($nature->isMandatory());
		$this->assertNull($nature->getStringValueObjectTypeName());

		$nature = $eiProps['holeradioObj']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $nature);
		$this->assertFalse($nature->isMultiline());
		$this->assertFalse($nature->isConstant());
		$this->assertFalse($nature->isReadOnly());
		$this->assertFalse($nature->isMandatory());
		$this->assertEquals(StrObjMock::class, $nature->getStringValueObjectTypeName());

		$nature = $eiProps['mandatoryHoleradioObj']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $nature);
		$this->assertFalse($nature->isMultiline());
		$this->assertFalse($nature->isConstant());
		$this->assertFalse($nature->isReadOnly());
		$this->assertTrue($nature->isMandatory());
		$this->assertEquals(StrObjMock::class, $nature->getStringValueObjectTypeName());

		$nature = $eiProps['annoHoleradioObj']->getNature();
		$this->assertInstanceOf(StringEiPropNature::class, $nature);
		$this->assertTrue($nature->isMultiline());
		$this->assertTrue($nature->isConstant());
		$this->assertTrue($nature->isReadOnly());
		$this->assertTrue($nature->isMandatory());
		$this->assertEquals(StrObjMock::class, $nature->getStringValueObjectTypeName());
	}


	function testQuickSearch() {
		$spec = SpecTestEnv::setUpSpec([StringTestObj::class]);


		$eiu = new Eiu($spec, TestEnv::getN2nContext());

		$this->assertTrue(true);

	}



}