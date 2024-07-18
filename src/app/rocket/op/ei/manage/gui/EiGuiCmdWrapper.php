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
namespace rocket\op\ei\manage\gui;

use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\DefPropPath;
use n2n\core\container\N2nContext;
use rocket\op\ei\component\prop\EiProp;
use rocket\ui\gui\UnresolvableDefPropPathExceptionEi;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\ui\gui\field\GuiField;
use rocket\ui\gui\control\GuiControl;
use rocket\op\ei\component\command\EiCmd;
use rocket\op\ei\EiCmdPath;

class EiGuiCmdWrapper {

	function __construct(private EiCmdPath $eiCmdPath, private EiGuiCmd $eiGuiCmd) {
	}
	
	/**
	 * @return EiPropPath
	 */
	function getEiCmdPath(): EiPropPath {
		return $this->eiCmdPath;
	}

	/**
	 * @param EiGuiDefinition $eiGuiDefinition
	 * @param N2nContext $n2nContext
	 * @return GuiControl[]
	 */
	function createGeneralGuiControls(EiGuiDefinition $eiGuiDefinition, N2nContext $n2nContext): array {
		return $this->eiGuiCmd->createGeneralGuiControls(new Eiu($eiGuiDefinition, $n2nContext));
	}

	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @return GuiControl[]
	 */
	function createEntryGuiControls(EiFrame $eiFrame, EiEntry $eiEntry): array {
		return $this->eiGuiCmd->createEntryGuiControls(new Eiu($eiGuiDefinition, $eiFrame, $eiEntry));
	}


	
//	/**
//	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 * @return DisplayDefinition|null
//	 */
//	function buildDisplayDefinition(EiGuiMaskDeclaration $eiGuiMaskDeclaration, bool $defaultDisplayedRequired) {
//		$displayDefinition = $this->eiGuiProp->buildDisplayDefinition(new Eiu($eiGuiMaskDeclaration, $this->eiPropPath));
//
//		if ($displayDefinition === null || ($defaultDisplayedRequired && !$displayDefinition->isDefaultDisplayed())) {
//			return null;
//		}
//
//		if ($displayDefinition->getOverwriteLabel() !== null && $displayDefinition->getOverwriteLabel() !== null) {
//			return $displayDefinition;
//		}
//
//		$n2nLocale = $eiGuiMaskDeclaration->getEiFrame()->getN2nContext()->getN2nLocale();
//
//		if ($displayDefinition->getLabel() === null) {
//			$displayDefinition->setLabel($this->getEiProp()->getLabelLstr()->t($n2nLocale));
//		}
//
//		if ($displayDefinition->getHelpText() === null
//				&& null !== ($helpTextLstr = $this->getEiProp()->getHelpTextLstr())) {
//			$displayDefinition->setHelpText($helpTextLstr->t($n2nLocale));
//		}
//
//		return $displayDefinition;
//	}
	
	function buildForkDisplayDefinition(DefPropPath $forkedDefPropPath, EiGuiMaskDeclaration $eiGuiMaskDeclaration, bool $defaultDisplayedRequired) {
		return $this->eiGuiCmd->getForkEiGuiDefinition()->getEiGuiPropWrapperByDefPropPath($forkedDefPropPath)
				->buildDisplayDefinition($eiGuiMaskDeclaration, $defaultDisplayedRequired);
	}
	
	/**
	 * @return DefPropPath[]
	 */
	function getForkedDefPropPaths() {
		$forkEiGuiDefinition = $this->eiGuiCmd->getForkEiGuiDefinition();
		
		if ($forkEiGuiDefinition === null) {
			return [];
		}
		
		return $forkEiGuiDefinition->getDefPropPaths();
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return \rocket\op\ei\manage\gui\GuiPropWrapper
	 *@throws UnresolvableDefPropPathExceptionEi
	 */
	function getForkedGuiPropWrapper(DefPropPath $defPropPath) {
		if (null !== ($forkEiGuiDefinition = $this->guiProp->getForkEiGuiDefinition())) {
			return $forkEiGuiDefinition->getGuiPropWrapperByDefPropPath($defPropPath);
		}
		
		throw new UnresolvableDefPropPathExceptionEi('GuiProp ' . $defPropPath . ' not found.');
	}

	/**
	 * @param N2nContext $n2nContext
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @param array|null $forkedDefPropPaths
	 * @return EiGuiPropSetup
	 */
	function buildGuiField(EiFrame $eiFrame, EiEntry $eiEntry, bool $readOnly, ?array $forkedDefPropPaths): ?GuiField {
		return $this->eiGuiCmd->buildGuiField(new Eiu($eiFrame, $this->eiGuiDefinition, $eiEntry, $this->eiCmdPath), $readOnly);
	}
	

	function getEiProp(): EiProp {
		return $this->eiGuiDefinition->getEiMask()->getEiPropCollection()->getByPath($this->eiCmdPath);
	}
	
	
}