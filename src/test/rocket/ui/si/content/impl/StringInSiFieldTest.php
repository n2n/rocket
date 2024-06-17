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

class StringInSiFieldTest extends TestCase {


	/**
	 * @throws CorruptedSiInputDataException
	 */
	function testWithoutModel(): void {
		$siField = SiFields::stringIn('holeradio');

		$siMaskIdentifier = new SiMaskIdentifier('mask-id', 'type-id');
		$siEntryIndentifier  = new SiEntryIdentifier($siMaskIdentifier, 1);
		$siEntryQualifier = new SiEntryQualifier($siMaskIdentifier, 1, 'holeradio');

		$siEntry = new SiEntry($siEntryQualifier);
		$siEntry->putField('holeradio', $siField);

		$siEntry->handleEntryInput(new SiEntryInput($siEntryIndentifier), $this->createMock(N2nContext::class));
	}
}