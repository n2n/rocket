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
use testmdl\string\bo\PathPartTestObj;
use rocket\op\ei\UnknownEiTypeException;

class PathPartEiPropNatureSetupTest extends TestCase {


	function setUp(): void {
		GeneralTestEnv::teardown();
	}

	/**
	 * @throws UnknownEiTypeException
	 */
	function testSetup(): void {
		$spec = SpecTestEnv::setUpSpec([PathPartTestObj::class]);
		$eiType = $spec->getEiTypeByClassName(PathPartTestObj::class);

		$eiProps = $eiType->getEiMask()->getEiPropCollection()->toArray();
		$this->assertCount(5, $eiProps);

		$nature = $eiProps['pathPart']->getNature();
		$this->assertInstanceOf(PathPartEiPropNature::class, $nature);
		$this->assertFalse($nature->isConstant());
		$this->assertFalse($nature->isReadOnly());
		$this->assertFalse($nature->isMandatory());

		$nature = $eiProps['mandatoryPathPart']->getNature();
		$this->assertInstanceOf(PathPartEiPropNature::class, $nature);
		$this->assertFalse($nature->isConstant());
		$this->assertFalse($nature->isReadOnly());
		$this->assertTrue($nature->isMandatory());

		$nature = $eiProps['annoPathPart']->getNature();
		$this->assertInstanceOf(PathPartEiPropNature::class, $nature);
		$this->assertTrue($nature->isConstant());
		$this->assertTrue($nature->isReadOnly());
		$this->assertTrue($nature->isMandatory());

	}


}