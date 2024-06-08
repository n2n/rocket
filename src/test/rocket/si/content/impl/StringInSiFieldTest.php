<?php

namespace rocket\si\content\impl;

use rocket\ui\si\content\SiEntry;
use rocket\ui\si\input\SiEntryInput;
use rocket\ui\si\content\SiEntryIdentifier;
use PHPUnit\Framework\TestCase;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\ui\si\content\impl\SiFields;

class StringInSiFieldTest extends TestCase {


	/**
	 * @throws CorruptedSiInputDataException
	 */
	function testWithoutModel(): void {
		$siField = SiFields::stringIn('holeradio');

		$siEntry = new SiEntry(null, null);
		$siEntry->putField('holeradio', $siField);

		$siEntry->handleEntryInput(new SiEntryInput($this->createMock(SiEntryIdentifier::class),
				'mask-id', true));
	}
}