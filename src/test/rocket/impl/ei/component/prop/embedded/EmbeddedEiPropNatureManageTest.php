<?php

namespace rocket\impl\ei\component\prop\embedded;

use PHPUnit\Framework\TestCase;
use n2n\test\TestEnv;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\embedded\bo\EmbeddingContainerTestObj;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\op\ei\EiPropPath;
use rocket\op\spec\Spec;
use testmdl\embedded\bo\EmbeddableTestObj;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\EiType;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\gui\field\GuiFieldPath;
use n2n\core\container\N2nContext;
use rocket\ui\si\input\SiEntryInput;
use rocket\ui\si\input\SiFieldInput;

class EmbeddedEiPropNatureManageTest extends TestCase {
	private Spec $spec;
	private EiType $eiType;
	private EiFrame $eiFrame;

	function setUp(): void {
		GeneralTestEnv::teardown();

		$this->spec = SpecTestEnv::setUpSpec([EmbeddingContainerTestObj::class]);

		$eiLaunch = new EiLaunch(TestEnv::getN2nContext(), new FullEiPermissionManager(), TestEnv::em());
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

		$eiFrameUtil = new EiFrameUtil($this->eiFrame);
		$eiGuiDeclaration = $eiFrameUtil->createEiGuiDeclaration($eiEntry->getEiMask(), true, false, null);
		$eiGuiValueBoundary = $eiGuiDeclaration->createGuiValueBoundary($this->eiFrame, [$eiEntry], false);

		$siEntryIdentifier = $eiGuiValueBoundary->getSelectedGuiEntry()->getSiEntryQualifier()->getIdentifier();
		$siEntryInput = new SiEntryInput($siEntryIdentifier);
		$guiFieldPath = new GuiFieldPath([(new EiPropPath(['reqEditEmbeddable', 'someProp']))->__toString()]);
		$siEntryInput->putFieldInput($guiFieldPath->__toString(), new SiFieldInput(['value' => 'some value']));

		$this->assertTrue($eiGuiValueBoundary->getSiValueBoundary()->handleInput($siEntryInput,
				$this->createMock(N2nContext::class)));

//		$guiField->getSiField()->handleInput(, $this->createMock(N2nContext::class));
//		$eiGuiValueBoundary->save($this->createMock(N2N_CRLF));

		$eiEntry->save();


		$embeddingContainerTestObj = $eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();
		assert($embeddingContainerTestObj instanceof EmbeddingContainerTestObj);
		$this->assertEquals('some value', $embeddingContainerTestObj->reqEditEmbeddable->someProp);

	}

}