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

namespace rocket\ui\si\content\impl;

use PHPUnit\Framework\TestCase;
use n2n\core\container\N2nContext;
use rocket\ui\si\err\CorruptedSiDataException;

class StringInSiFieldTest extends TestCase {

	/**
	 * @throws CorruptedSiDataException
	 */
	function testHandleInput(): void {
		$siField = SiFields::stringIn('holeradio1');

		$this->assertEquals('holeradio1', $siField->getValue());

		$siField->handleInput(['value' => 'holeradio2'], $this->createMock(N2nContext::class));

		$this->assertEquals('holeradio2', $siField->getValue());
	}

	function testTypeAndData(): void {
		$this->markTestSkipped('TODO');
	}
}