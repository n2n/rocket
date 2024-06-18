<?php

namespace rocket\ui\si\content\impl;

use rocket\ui\si\content\SiEntry;
use rocket\ui\si\input\SiEntryInput;
use rocket\ui\si\content\SiEntryIdentifier;
use PHPUnit\Framework\TestCase;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\si\content\SiEntryQualifier;
use n2n\core\container\N2nContext;
use n2n\util\type\attrs\AttributesException;

class StringInSiFieldTest extends TestCase {

	function testHandleInput(): void {
		$siField = SiFields::stringIn('holeradio1');

		$this->assertEquals('holeradio1', $siField->getValue());

		$siField->handleInput(['value' => 'holeradio2'], $this->createMock(N2nContext::class));

		$this->assertEquals('holeradio2', $siField->getValue());
	}

	function testTypeAndData(): void {
		// TODO
	}
}