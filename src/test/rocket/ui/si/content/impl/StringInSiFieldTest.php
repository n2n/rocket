<?php

namespace rocket\ui\si\content\impl;

use PHPUnit\Framework\TestCase;
use n2n\core\container\N2nContext;

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