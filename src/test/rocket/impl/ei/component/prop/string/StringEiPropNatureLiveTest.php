<?php

namespace rocket\impl\ei\component\prop\string;

use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use n2n\test\TestEnv;
use PHPUnit\Framework\TestCase;
use n2n\l10n\N2nLocale;
use testmdl\string\bo\StringTestObj;
use testmdl\test\string\StringTestEnv;
use testmdl\string\bo\StrObjMock;
use rocket\op\ei\EiPropPath;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\op\ei\manage\security\InaccessibleEiFieldException;
use rocket\ui\gui\ViewMode;
use rocket\ui\si\content\impl\StringInSiField;
use rocket\op\ei\manage\DefPropPath;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\gui\field\GuiFieldPath;
use rocket\ui\si\input\SiEntryInput;
use rocket\ui\si\input\SiFieldInput;
use rocket\ui\si\input\CorruptedSiInputDataException;
use n2n\core\container\N2nContext;

class StringEiPropNatureLiveTest extends TestCase {


	private Spec $spec;

	private int $stringTestObj1Id;
//	private int $translationTestObj1Id;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([StringTestObj::class]);

		$tx = TestEnv::createTransaction();
		$stringTestObj = StringTestEnv::setUpStringTestObj();
		$stringTestObj->mandatoryHoleradio = 'holeradio value';
		$stringTestObj->mandatoryHoleradioObj = new StrObjMock('value');
		$tx->commit();

		$this->stringTestObj1Id = $stringTestObj->id;
	}

	function testEiField(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findStringTestObj($this->stringTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$this->assertNull($eiEntry->getValue(new EiPropPath(['holeradio'])));
		$this->assertEquals('holeradio value', $eiEntry->getValue(new EiPropPath(['mandatoryHoleradio'])));
		$this->assertNull($eiEntry->getValue(new EiPropPath(['annoHoleradio'])));
		$this->assertNull($eiEntry->getValue(new EiPropPath(['holeradioObj'])));
		$this->assertEquals(new StrObjMock('value'),
				$eiEntry->getValue(new EiPropPath(['mandatoryHoleradioObj'])));
		$this->assertNull($eiEntry->getValue(new EiPropPath(['annoHoleradioObj'])));

		$eiEntry->setValue(new EiPropPath(['holeradio']), 'new-value');
		$this->assertFalse($eiEntry->getEiField(new EiPropPath(['annoHoleradio']))->isWritable(false));
		$eiEntry->setValue(new EiPropPath(['annoHoleradio']), 'new-anno-value');
		$eiEntry->setValue(new EiPropPath(['holeradioObj']), new StrObjMock('new-v'));
		$this->assertFalse($eiEntry->getEiField(new EiPropPath(['annoHoleradioObj']))->isWritable(false));
		$eiEntry->setValue(new EiPropPath(['annoHoleradioObj']), new StrObjMock('new-ov'));


		$this->assertTrue($eiEntry->save());

		/**
		 * @var StringTestObj $stringTestObj
		 */
		$stringTestObj = $eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();

		$this->assertEquals('new-value', $stringTestObj->holeradio);
		$this->assertEquals('holeradio value', $stringTestObj->mandatoryHoleradio);
		$this->assertNull($stringTestObj->annoHoleradio);
		$this->assertEquals(new StrObjMock('new-v'), $stringTestObj->holeradioObj);
		$this->assertEquals(new StrObjMock('value'), $stringTestObj->mandatoryHoleradioObj);
		$this->assertNull($stringTestObj->annoHoleradioObj);
	}

	function testEiFieldValidator(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findStringTestObj($this->stringTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['holeradio']))->isValid());
		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['mandatoryHoleradio']))->isValid());
		$this->assertFalse($eiEntry->getEiFieldNature(new EiPropPath(['annoHoleradio']))->isValid());
		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['holeradioObj']))->isValid());
		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['mandatoryHoleradioObj']))->isValid());
		$this->assertFalse($eiEntry->getEiFieldNature(new EiPropPath(['annoHoleradioObj']))->isValid());

		$this->assertFalse($eiEntry->save());

		$this->assertCount(2, $eiEntry->getValidationResult()->getMessages(true));

		$result = $eiEntry->getValidationResult()->getEiFieldValidationResult(new EiPropPath(['annoHoleradio']));
		$this->assertStringContainsStringIgnoringCase('mandatory', (string) $result->getMessages(true)[0]);
		$result = $eiEntry->getValidationResult()->getEiFieldValidationResult(new EiPropPath(['annoHoleradioObj']));
		$this->assertStringContainsStringIgnoringCase('mandatory', (string) $result->getMessages(true)[0]);
	}

	/**
	 * @return void
	 * @throws InaccessibleEiFieldException
	 */
	function testEiFieldConstraint(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findStringTestObj($this->stringTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		try {
			$eiEntry->setValue(new EiPropPath(['holeradio']), new StrObjMock('value'));
			$this->fail('value must be incompatible');
		} catch (ValueIncompatibleWithConstraintsException $e) {
			$this->assertTrue(true);
		}

		try {
			$eiEntry->setValue(new EiPropPath(['annoHoleradio']), new StrObjMock('value'));
			$this->fail('value must be incompatible');
		} catch (ValueIncompatibleWithConstraintsException $e) {
			$this->assertTrue(true);
		}

		try {
			$eiEntry->setValue(new EiPropPath(['holeradioObj']), 'value');
			$this->fail('value must be incompatible');
		} catch (ValueIncompatibleWithConstraintsException $e) {
			$this->assertTrue(true);
		}
	}

	function testIdentityString(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);

		$eiObject = $eiType->createEiObject(StringTestEnv::findStringTestObj($this->stringTestObj1Id));

		$is = $eiType->getEiMask()->getEiEngine()->getIdNameDefinition()->createIdentityStringFromPattern(
				'hui: {mandatoryHoleradio} / {mandatoryHoleradioObj}', TestEnv::getN2nContext(), $eiObject,
				N2nLocale::getDefault());

		$this->assertEquals('hui: holeradio value / value', $is);
	}

	/**
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	function testScalar(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);

		$props = $eiType->getEiMask()->getEiEngine()->getScalarEiDefinition()->getScalarEiProperties();
		$this->assertcount(6, $props);

		$this->assertEquals('value', $props['holeradio']->eiFieldValueToScalarValue('value'));
		$this->assertEquals('value', $props['holeradio']->scalarValueToEiFieldValue('value'));

		$this->assertEquals('value', $props['holeradioObj']->eiFieldValueToScalarValue(new StrObjMock('value')));
		$this->assertEquals(new StrObjMock('value'), $props['holeradioObj']->scalarValueToEiFieldValue('value'));
	}

	function testSiEntry(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findStringTestObj($this->stringTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$eiGuiEntry = $eiType->getEiMask()->getEiEngine()
				->obtainEiGuiMaskDeclaration(ViewMode::BULKY_EDIT, null)
				->createGuiEntry($eiFrame, $eiEntry, false);


		$siEntry = $eiGuiEntry->getSiEntry(N2nLocale::getDefault());
		$fields = $siEntry->getFields();
		$this->assertCount(6, $fields);

		$this->assertTrue(assert($fields['holeradio'] instanceof StringInSiField));
		$this->assertFalse($fields['holeradio']->isReadOnly());
		$this->assertFalse($fields['holeradio']->isMandatory());
		$this->assertNull( $fields['holeradio']->getValue());

		$this->assertTrue(assert($fields['mandatoryHoleradio'] instanceof StringInSiField));
		$this->assertFalse($fields['mandatoryHoleradio']->isReadOnly());
		$this->assertTrue($fields['mandatoryHoleradio']->isMandatory());
		$this->assertEquals('holeradio value', $fields['mandatoryHoleradio']->getValue());

		$this->assertTrue(assert($fields['annoHoleradio'] instanceof StringInSiField));
		$this->assertTrue($fields['annoHoleradio']->isReadOnly());
		$this->assertNull( $fields['annoHoleradio']->getValue());

		$this->assertTrue(assert($fields['holeradioObj'] instanceof StringInSiField));
		$this->assertFalse($fields['holeradioObj']->isReadOnly());
		$this->assertFalse($fields['holeradioObj']->isMandatory());
		$this->assertNull($fields['holeradioObj']->getValue());

		$this->assertTrue(assert($fields['mandatoryHoleradioObj'] instanceof StringInSiField));
		$this->assertFalse($fields['mandatoryHoleradioObj']->isReadOnly());
		$this->assertTrue($fields['mandatoryHoleradioObj']->isMandatory());
		$this->assertEquals('value', $fields['mandatoryHoleradioObj']->getValue());

		$this->assertTrue(assert($fields['annoHoleradioObj'] instanceof StringInSiField));
		$this->assertTrue($fields['annoHoleradioObj']->isReadOnly());
		$this->assertNull( $fields['annoHoleradioObj']->getValue());
	}

	/**
	 * @throws AttributesException
	 * @throws CorruptedSiInputDataException
	 */
	function testSiEntryInput(): void {
		$eiType = $this->spec->getEiTypeByClassName(StringTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findStringTestObj($this->stringTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$guiEntry = $eiType->getEiMask()->getEiEngine()
				->obtainEiGuiMaskDeclaration(ViewMode::BULKY_EDIT, null)
				->createGuiEntry($eiFrame, $eiEntry, false);

		$siEntryInput = new SiEntryInput($guiEntry->getSiEntry()->getQualifier()->getIdentifier());
		$siEntryInput->putFieldInput('holeradio', new SiFieldInput(['value' => 'new-value']));
		$siEntryInput->putFieldInput('holeradioObj', new SiFieldInput(['value' => 'new-ov']));

		$this->assertTrue($guiEntry->getSiEntry()->handleEntryInput($siEntryInput,
				$this->createMock(N2nContext::class)));

		$this->assertEquals('new-value', $eiEntry->getValue(new EiPropPath(['holeradio'])));
		$this->assertEquals(new StrObjMock('new-ov'), $eiEntry->getValue(new EiPropPath(['holeradioObj'])));
	}
}
