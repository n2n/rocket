<?php

namespace rocket\op\ei\manage\gui\factory;

use PHPUnit\Framework\TestCase;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\EiType;
use rocket\ui\si\control\SiIconType;
use rocket\op\ei\manage\gui\factory\mock\EiPropNatureMock;
use rocket\ui\gui\GuiProp;
use n2n\test\TestEnv;
use rocket\ui\gui\ViewMode;
use rocket\impl\ei\component\prop\adapter\gui\EiGuiPropProxy;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\manage\gui\DisplayDefinition;
use rocket\op\ei\mask\model\DisplayScheme;
use rocket\op\ei\mask\model\DisplayStructure;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\EiPropPath;
use rocket\ui\si\meta\SiStructureType;

class EiGuiDefinitionFactoryTest extends TestCase {

	function testIsEmpty(): void {
		$eiMask = new EiMask($this->createMock(EiType::class), 'Holeradio', 'Holeradios',
				SiIconType::ICON_AD);

		$prop1Path = new DefPropPath([new EiPropPath(['prop1'])]);
		$prop2Path = new DefPropPath([new EiPropPath(['prop2'])]);

		$displayScheme = new DisplayScheme();
		$displayScheme->setBulkyDisplayStructure((new DisplayStructure())
				->addDefPropPath($prop1Path, SiStructureType::ITEM)
				->addDefPropPath($prop2Path, SiStructureType::ITEM));
		$eiMask->setDisplayScheme($displayScheme);

		$mock = new EiPropNatureMock();
		$mock->buildEiGuiPropClosure = fn () => new EiGuiPropProxy(
				fn () => $this->createMock(GuiField::class),
				new DisplayDefinition(null, true));

		$eiMask->getEiPropCollection()->add('prop1', $mock);
		$eiMask->getEiPropCollection()->add('prop2', new EiPropNatureMock());

		$factory = new EiGuiDefinitionFactory($eiMask, TestEnv::getN2nContext());

		$eiGuiDefinition = $factory->createEiGuiDefinition(ViewMode::BULKY_READ, null);
		$this->assertTrue($eiGuiDefinition->getEiGuiPropMap()->containsDefPropPath($prop1Path));
		$this->assertTrue($eiGuiDefinition->getEiGuiPropMap()->containsDefPropPath($prop2Path));
	}
}
