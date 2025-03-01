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

namespace rocket\impl\ei\component\prop\embedded;

use PHPUnit\Framework\TestCase;
use n2n\test\TestEnv;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\embedded\bo\EmbeddingContainerTestObj;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\op\ei\EiPropPath;
use rocket\op\spec\Spec;
use testmdl\embedded\bo\EmbeddableTestObj;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\EiType;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\gui\field\GuiPropPath;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\request\SiEntryInput;
use rocket\ui\si\api\request\SiFieldInput;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\manage\gui\factory\EiGuiValueBoundaryFactory;
use rocket\ui\si\api\request\SiValueBoundaryInput;

class EmbeddedEiPropNatureManageTest extends TestCase {
	private Spec $spec;
	private EiType $eiType;
	private EiFrame $eiFrame;

	function setUp(): void {
		GeneralTestEnv::teardown();

		$this->spec = SpecTestEnv::setUpSpec([EmbeddingContainerTestObj::class]);

		$eiLaunch = SpecTestEnv::setUpEiLaunch($this->spec);
		$this->eiType = $this->spec->getEiTypeByClassName(EmbeddingContainerTestObj::class);
		$eiMask = $this->eiType->getEiMask();

		$this->eiFrame = $eiLaunch->createRootEiFrame($eiMask->getEiEngine());
		$this->eiFrame->exec($eiMask->getEiCmdCollection()->determineGenericOverview(true)->getEiCmd());
	}

	function createEiEntry(): EiEntry {
		$eiObject = $this->eiType->createNewEiObject();
		$embeddingContainerTestObj = $eiObject->getEiEntityObj()->getEntityObj();
		assert($embeddingContainerTestObj instanceof EmbeddingContainerTestObj);
		$embeddingContainerTestObj->optEditEmbeddable = new EmbeddableTestObj();

		return $this->eiFrame->createEiEntry($eiObject);
	}

	function testEiField(): void {
		$eiEntry = $this->createEiEntry();

		$this->assertInstanceOf(EmbeddedEiField::class, $eiEntry->getEiFieldNature(new EiPropPath(['reqEditEmbeddable'])));
		$this->assertNotNull($eiEntry->getValue(new EiPropPath(['reqEditEmbeddable'])));
		$this->assertNull($eiEntry->getValue(new EiPropPath(['reqEditEmbeddable', 'someProp'])));

		$eiEntry->setValue(new EiPropPath(['reqEditEmbeddable', 'someProp']), 'some value');

		$eiEntry->save();

		$this->assertTrue($eiEntry->save());

		$embeddingContainerTestObj = $eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();
		assert($embeddingContainerTestObj instanceof EmbeddingContainerTestObj);
		$this->assertEquals('some value', $embeddingContainerTestObj->reqEditEmbeddable->someProp);
	}

	/**
	 * @throws CorruptedSiDataException
	 * @throws AttributesException
	 */
	function testEiGuiProp(): void {
		$eiEntry = $this->createEiEntry();

		$eiFrameUtil = new EiObjectSelector($this->eiFrame);
//		$eiGuiDeclaration = $eiFrameUtil->createEiGuiDeclaration($eiEntry->getEiMask(), true, false, null);

//		$eiGuiDefinition = $eiEntry->getEiMask()->getEiEngine()->getEiGuiDefinition(ViewMode::determine(true, false, true))

		$factory = new EiGuiValueBoundaryFactory($this->eiFrame);
		$guiValueBoundary = $factory->create(null, [$eiEntry],
				ViewMode::determine(true, false, true));

		$siEntryIdentifier = $guiValueBoundary->getSelectedGuiEntry()->getSiEntryQualifier()->getIdentifier();
		$siEntryInput = new SiEntryInput($siEntryIdentifier->getMaskIdentifier()->getId(), null);
		$siGuiValueBoundaryInput = new SiValueBoundaryInput(
				$guiValueBoundary->getSiValueBoundary()->getSelectedTypeId(),
				$siEntryInput);
		$guiFieldPath = new GuiPropPath([(new EiPropPath(['reqEditEmbeddable', 'someProp']))->toGuiFieldKey()]);
		$siEntryInput->putFieldInput($guiFieldPath->__toString(), new SiFieldInput(['value' => 'some value']));

		$this->assertTrue($guiValueBoundary->getSiValueBoundary()->handleInput($siGuiValueBoundaryInput,
				$this->createMock(N2nContext::class)));

//		$guiField->getSiField()->handleInput(, $this->createMock(N2nContext::class));
//		$eiGuiValueBoundary->save($this->createMock(N2N_CRLF));

		$eiEntry->save();


		$embeddingContainerTestObj = $eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();
		assert($embeddingContainerTestObj instanceof EmbeddingContainerTestObj);
		$this->assertEquals('some value', $embeddingContainerTestObj->reqEditEmbeddable->someProp);

	}

}