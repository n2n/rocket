<?php

namespace rocket\impl\ei\component\prop\embedded;

use PHPUnit\Framework\TestCase;
use n2n\test\TestEnv;
use rocket\test\GeneralTestEnv;
use rocket\test\SpecTestEnv;
use testmdl\enum\bo\EnumTestObj;
use rocket\impl\ei\component\prop\enum\EnumEiPropNature;
use testmdl\embedded\bo\EmbeddingContainerTestObj;
use rocket\impl\ei\component\prop\string\StringEiPropNature;
use rocket\op\ei\manage\EiLaunch;
use rocket\user\model\security\FullEiPermissionManager;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\op\ei\EiPropPath;
use rocket\op\spec\Spec;
use rocket\op\ei\manage\DefPropPath;
use testmdl\embedded\bo\EmbeddableTestObj;
use n2n\persistence\orm\attribute\N2nLocale;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\EiType;
use rocket\op\ei\manage\frame\EiFrame;

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

	function testGuiProp(): void {
		$eiEntry = $this->createEiEntry();

		$eiFrameUtil = new EiFrameUtil($this->eiFrame);
		$eiGuiDeclaration = $eiFrameUtil->createEiGuiDeclaration($eiEntry->getEiMask(), true, false, null);
		$eiGuiValueBoundary = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame, [$eiEntry], false);

		$guiField = $eiGuiValueBoundary->getSelectedEiGuiEntry()->getGuiFieldByDefPropPath(
				new DefPropPath([new EiPropPath(['reqEditEmbeddable', 'someProp'])]));

		$guiField->getSiField()->handleInput(['value' => 'some value']);
		$eiGuiValueBoundary->save();

		$eiEntry->save();


		$embeddingContainerTestObj = $eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();
		assert($embeddingContainerTestObj instanceof EmbeddingContainerTestObj);
		$this->assertEquals('some value', $embeddingContainerTestObj->reqEditEmbeddable->someProp);

	}

}