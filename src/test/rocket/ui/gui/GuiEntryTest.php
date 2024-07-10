<?php

namespace rocket\ui\gui;

use PHPUnit\Framework\TestCase;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\si\api\request\SiFieldInput;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\l10n\N2nLocale;
use n2n\core\container\N2nContext;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\ui\si\content\SiEntryIdentifier;
use rocket\ui\gui\field\GuiFieldMap;
use rocket\ui\gui\field\GuiFieldModel;

class GuiEntryTest extends TestCase {

	private static SiMaskIdentifier $siMaskIdentifier1;
	private static SiMaskQualifier $siMaskQualifier1;
	private static SiEntryIdentifier $siEntryIdentifier1;
	private static SiEntryQualifier $siEntryQualifier1;

	static function setUpBeforeClass(): void {
		self::$siMaskIdentifier1 = new SiMaskIdentifier('mask-1', 'type-1');
		self::$siMaskQualifier1 = new SiMaskQualifier(self::$siMaskIdentifier1, 'Mask 1', 'icon-1');
		self::$siEntryIdentifier1 = new SiEntryIdentifier(self::$siMaskIdentifier1, 2);
		self::$siEntryQualifier1 = new SiEntryQualifier(self::$siMaskIdentifier1, 2, 'entry-1');
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	function testHandle(): void {
		$guiEntry = new GuiEntry(self::$siEntryQualifier1);

		$guiFieldModel = new GuiFieldModelMock();

		$guiFieldMap = new GuiFieldMap();
		$guiFieldMap->putGuiField('prop1', GuiFields::stringIn(true)->setValue('old-value')
				->setModel($guiFieldModel));
		$guiEntry->init($guiFieldMap, null);

		$siEntryInput = new \rocket\ui\si\api\request\SiEntryInput(self::$siEntryIdentifier1);
		$siEntryInput->putFieldInput('prop1', new SiFieldInput(['value' => 'new-value']));

		$this->assertTrue($guiEntry->getSiEntry(N2nLocale::getDefault())->handleEntryInput($siEntryInput,
				$this->createMock(N2nContext::class)));

//		$this->assertTrue($guiEntry->save($this->createMock(N2nContext::class)));

		$guiField = $guiEntry->getGuiFieldMap()->getGuiField('prop1');
		$this->assertEquals('new-value', $guiField->getValue());
		$this->assertEquals('new-value prepared', $guiFieldModel->savedValue);
	}

}

class GuiFieldModelMock implements GuiFieldModel  {

	public mixed $preparedValue = null;
	public mixed $savedValue = null;

	function getMessages(): array {
		return [];
	}

	function handleInput(mixed $value, N2nContext $n2nContext): bool {
		$this->preparedValue = $value . ' prepared';
		return true;
	}

	function save(N2nContext $n2nContext): void {
		$this->savedValue = $this->preparedValue ;
	}
}