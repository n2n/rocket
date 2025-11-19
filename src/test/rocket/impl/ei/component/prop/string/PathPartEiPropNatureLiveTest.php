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

use rocket\op\spec\Spec;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use n2n\test\TestEnv;
use PHPUnit\Framework\TestCase;
use n2n\l10n\N2nLocale;
use testmdl\string\bo\PathPartTestObj;
use testmdl\test\string\StringTestEnv;
use testmdl\string\bo\StrObjMock;
use rocket\op\ei\EiPropPath;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\op\ei\manage\security\InaccessibleEiFieldException;
use rocket\ui\gui\ViewMode;
use rocket\ui\si\content\impl\StringInSiField;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\api\request\SiEntryInput;
use rocket\ui\si\api\request\SiFieldInput;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\gui\factory\EiGuiEntryFactory;
use rocket\op\ei\UnknownEiTypeException;
use rocket\op\ei\manage\entry\UnknownEiFieldExcpetion;
use rocket\ui\si\content\impl\string\PathPartInSiField;
use rocket\ui\si\content\impl\StringOutSiField;

class PathPartEiPropNatureLiveTest extends TestCase {


	private Spec $spec;

	private int $pathPartTestObj1Id;
//	private int $translationTestObj1Id;

	function setUp(): void {
		GeneralTestEnv::teardown();
		$this->spec = SpecTestEnv::setUpSpec([PathPartTestObj::class]);

		$tx = TestEnv::createTransaction();
		$pathPartTestObj = StringTestEnv::setUpPathPartTestObj();
		$pathPartTestObj2 = StringTestEnv::setUpPathPartTestObj();
		$pathPartTestObj2->name = 'Holeradio 3';
		$pathPartTestObj2->uniquePerPathPart = 'path-part-2';
		$tx->commit();

		$this->pathPartTestObj1Id = $pathPartTestObj->id;
	}

	/**
	 * @throws ValueIncompatibleWithConstraintsException
	 * @throws UnknownEiTypeException
	 * @throws InaccessibleEiFieldException
	 * @throws UnknownEiFieldExcpetion
	 */
	function testEiField(): void {
		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$this->assertNull($eiEntry->getValue(new EiPropPath(['pathPart'])));
		$this->assertEquals('mandatory-holeradio', $eiEntry->getValue(new EiPropPath(['mandatoryPathPart'])));
		$this->assertEquals('anno-holeradio', $eiEntry->getValue(new EiPropPath(['annoPathPart'])));

		$eiEntry->setValue(new EiPropPath(['pathPart']), 'new-holeradio');
		$eiEntry->setValue(new EiPropPath(['mandatoryPathPart']), 'new-mandatory-holeradio');
		$eiEntry->setValue(new EiPropPath(['annoPathPart']), 'new-anno-holeradio');

		$this->assertTrue($eiEntry->save());

		/**
		 * @var PathPartTestObj $pathPartTestObj
		 */
		$pathPartTestObj = $eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();

		$this->assertEquals('new-holeradio', $pathPartTestObj->pathPart);
		$this->assertEquals('new-mandatory-holeradio', $pathPartTestObj->mandatoryPathPart);
		// because read only
		$this->assertEquals('anno-holeradio', $pathPartTestObj->annoPathPart);
	}

	function testEiFieldValidator(): void {
		$this->markTestSkipped('todo');
//		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);
//		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());
//
//		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));
//		$eiEntry = $eiFrame->createEiEntry($eiObject);
//		$eiEntry->setValue(new EiPropPath(['annoHoleradio']), null);
//		$eiEntry->setValue(new EiPropPath(['annoHoleradioObj']), null);
//
//		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['holeradio']))->isValid());
//		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['mandatoryHoleradio']))->isValid());
//		$this->assertFalse($eiEntry->getEiFieldNature(new EiPropPath(['annoHoleradio']))->isValid());
//		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['holeradioObj']))->isValid());
//		$this->assertTrue($eiEntry->getEiFieldNature(new EiPropPath(['mandatoryHoleradioObj']))->isValid());
//		$this->assertFalse($eiEntry->getEiFieldNature(new EiPropPath(['annoHoleradioObj']))->isValid());
//
//		$this->assertFalse($eiEntry->save());
//
//		$this->assertCount(2, $eiEntry->getValidationResult()->getMessages(true));
//
//		$result = $eiEntry->getValidationResult()->getEiFieldValidationResult(new EiPropPath(['annoHoleradio']));
//		$this->assertStringContainsStringIgnoringCase('mandatory', (string) $result->getMessages(true)[0]);
//		$result = $eiEntry->getValidationResult()->getEiFieldValidationResult(new EiPropPath(['annoHoleradioObj']));
//		$this->assertStringContainsStringIgnoringCase('mandatory', (string) $result->getMessages(true)[0]);
	}

	/**
	 * @return void
	 * @throws InaccessibleEiFieldException
	 */
	function testEiFieldConstraint(): void {
		$this->markTestSkipped('todo');

//		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);
//		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());
//
//		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));
//		$eiEntry = $eiFrame->createEiEntry($eiObject);
//
//		try {
//			$eiEntry->setValue(new EiPropPath(['holeradio']), new StrObjMock('value'));
//			$this->fail('value must be incompatible');
//		} catch (ValueIncompatibleWithConstraintsException $e) {
//			$this->assertTrue(true);
//		}
//
//		try {
//			$eiEntry->setValue(new EiPropPath(['annoHoleradio']), new StrObjMock('value'));
//			$this->fail('value must be incompatible');
//		} catch (ValueIncompatibleWithConstraintsException $e) {
//			$this->assertTrue(true);
//		}
//
//		try {
//			$eiEntry->setValue(new EiPropPath(['holeradioObj']), 'value');
//			$this->fail('value must be incompatible');
//		} catch (ValueIncompatibleWithConstraintsException $e) {
//			$this->assertTrue(true);
//		}
	}

	function testIdentityString(): void {
		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);

		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));

		$is = $eiType->getEiMask()->getEiEngine()->getIdNameDefinition()->createIdentityStringFromPattern(
				'hui: {mandatoryPathPart}', TestEnv::getN2nContext(), $eiObject,
				N2nLocale::getDefault());

		$this->assertEquals('hui: mandatory-holeradio', $is);
	}

	/**
	 * @throws ValueIncompatibleWithConstraintsException
	 * @throws UnknownEiTypeException
	 */
	function testScalar(): void {
		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);

		$props = $eiType->getEiMask()->getEiEngine()->getScalarEiDefinition()->getScalarEiProperties();
		$this->assertcount(5, $props);

		$this->assertEquals('value', $props['pathPart']->eiFieldValueToScalarValue('value'));
		$this->assertEquals('value', $props['pathPart']->scalarValueToEiFieldValue('value'));

//		$this->assertEquals('value', $props['holeradioObj']->eiFieldValueToScalarValue(new StrObjMock('value')));
//		$this->assertEquals(new StrObjMock('value'), $props['holeradioObj']->scalarValueToEiFieldValue('value'));
	}

	/**
	 * @throws UnknownEiTypeException
	 */
	function testSiEntry(): void {
		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$eiGuiEntry = (new EiGuiEntryFactory($eiFrame))
				->createGuiEntry($eiEntry, ViewMode::BULKY_EDIT, false);

		$siEntry = $eiGuiEntry->getSiEntry(N2nLocale::getDefault());
		$fields = $siEntry->getFields();
		$this->assertCount(5, $fields);

		$this->assertTrue(assert($fields['pathPart'] instanceof PathPartInSiField));
		$this->assertFalse($fields['pathPart']->isReadOnly());
		$this->assertFalse($fields['pathPart']->isMandatory());
		$this->assertNull($fields['pathPart']->getValue());

		$this->assertTrue(assert($fields['mandatoryPathPart'] instanceof PathPartInSiField));
		$this->assertFalse($fields['mandatoryPathPart']->isReadOnly());
		$this->assertTrue($fields['mandatoryPathPart']->isMandatory()); // because field is generated
		$this->assertEquals('mandatory-holeradio', $fields['mandatoryPathPart']->getValue());

		$this->assertTrue(assert($fields['annoPathPart'] instanceof StringOutSiField));
		$this->assertTrue($fields['annoPathPart']->isReadOnly());
		$this->assertEquals('anno-holeradio', $fields['annoPathPart']->getValue());

//		$this->assertTrue(assert($fields['holeradioObj'] instanceof StringInSiField));
//		$this->assertFalse($fields['holeradioObj']->isReadOnly());
//		$this->assertFalse($fields['holeradioObj']->isMandatory());
//		$this->assertNull($fields['holeradioObj']->getValue());
//
//		$this->assertTrue(assert($fields['mandatoryHoleradioObj'] instanceof StringInSiField));
//		$this->assertFalse($fields['mandatoryHoleradioObj']->isReadOnly());
//		$this->assertTrue($fields['mandatoryHoleradioObj']->isMandatory());
//		$this->assertEquals('value', $fields['mandatoryHoleradioObj']->getValue());
//
//		$this->assertTrue(assert($fields['annoHoleradioObj'] instanceof StringInSiField));
//		$this->assertTrue($fields['annoHoleradioObj']->isReadOnly());
//		$this->assertEquals('default', $fields['annoHoleradioObj']->getValue());
	}

	/**
	 * @throws AttributesException
	 * @throws CorruptedSiDataException
	 * @throws UnknownEiTypeException
	 * @throws UnknownEiFieldExcpetion
	 */
	function testSiEntryInput(): void {
		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);
		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());

		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));
		$eiEntry = $eiFrame->createEiEntry($eiObject);

		$guiEntry = (new EiGuiEntryFactory($eiFrame))
				->createGuiEntry($eiEntry, ViewMode::BULKY_EDIT, false);

		$siEntryIdentifier = $guiEntry->getSiEntry()->getQualifier()->getIdentifier();
		$siEntryInput = new SiEntryInput($siEntryIdentifier->getMaskIdentifier()->getId(),
				$siEntryIdentifier->getId());
		$siEntryInput->putFieldInput('name', new SiFieldInput(['value' => 'Pretty Name like Holeradio']));
		$siEntryInput->putFieldInput('pathPart', new SiFieldInput(['value' => null]));
		$siEntryInput->putFieldInput('mandatoryPathPart', new SiFieldInput(['value' => 'other-path-part']));
		$siEntryInput->putFieldInput('uniquePerPathPart', new SiFieldInput(['value' => 'sanitized-path-part']));

		$this->assertTrue($guiEntry->getSiEntry()->handleEntryInput($siEntryInput,
				$this->createMock(N2nContext::class)));


		$this->assertNull($eiEntry->getValue(new EiPropPath(['pathPart'])));
//		$this->assertEquals('pretty-name-like-holeradio', $eiEntry->getValue(new EiPropPath(['mandatoryPathPart'])));
		$this->assertEquals('sanitized-path-part', $eiEntry->getValue(new EiPropPath(['uniquePerPathPart'])));
	}

//	function testSiEntryUniquePer(): void {
//		$eiType = $this->spec->getEiTypeByClassName(PathPartTestObj::class);
//		$eiFrame = SpecTestEnv::setUpEiFrame($this->spec, $eiType->getEiMask());
//
//		$eiObject = $eiType->createEiObject(StringTestEnv::findPathPartTestObj($this->pathPartTestObj1Id));
//		$eiEntry = $eiFrame->createEiEntry($eiObject);
//
//		$guiEntry = (new EiGuiEntryFactory($eiFrame))
//				->createGuiEntry($eiEntry, ViewMode::BULKY_EDIT, false);
//
//		$siEntryIndentifier = $guiEntry->getSiEntry()->getQualifier()->getIdentifier();
//		$siEntryInput = new SiEntryInput($siEntryIndentifier->getMaskIdentifier()->getId(),
//				$siEntryIndentifier->getId());
//		$siEntryInput->putFieldInput('uniquePerPathPart', new SiFieldInput(['value' => 'path-part-2']));
//
//		$this->assertFalse($guiEntry->getSiEntry()->handleEntryInput($siEntryInput,
//				$this->createMock(N2nContext::class)));
//
//
//
//		$this->assertNull($eiEntry->getValue(new EiPropPath(['pathPart'])));
////		$this->assertEquals('pretty-name-like-holeradio', $eiEntry->getValue(new EiPropPath(['mandatoryPathPart'])));
//		$this->assertEquals('not-saenaetized-work-todo', $eiEntry->getValue(new EiPropPath(['uniquePerPathPart'])));
//	}
}
