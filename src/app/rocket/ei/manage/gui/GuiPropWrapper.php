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
namespace rocket\ei\manage\gui;

use rocket\ei\EiPropPath;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\EiProp;
use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\core\container\N2nContext;

class GuiPropWrapper {
	
	private $guiDefinition;
	private $eiPropPath;
	private $guiProp;
	
	/**
	 * @param GuiDefinition $guiDefinition
	 * @param EiPropPath $eiPropPath
	 * @param GuiProp $guiProp
	 */
	function __construct(GuiDefinition $guiDefinition, EiPropPath $eiPropPath, GuiProp $guiProp) {
		$this->guiDefinition = $guiDefinition;
		$this->eiPropPath = $eiPropPath;
		$this->guiProp = $guiProp;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @return DisplayDefinition|null
	 */
	function buildDisplayDefinition(EiGuiFrame $eiGuiFrame, bool $defaultDisplayedRequired) {
		$displayDefinition = $this->guiProp->buildDisplayDefinition(new Eiu($eiGuiFrame, $this->eiPropPath));
		
		if ($displayDefinition === null || ($defaultDisplayedRequired && !$displayDefinition->isDefaultDisplayed())) {
			return null;
		}
		
		if ($displayDefinition->getOverwriteLabel() !== null && $displayDefinition->getOverwriteLabel() !== null) {
			return $displayDefinition;
		}
		
		$n2nLocale = $eiGuiFrame->getEiFrame()->getN2nContext()->getN2nLocale();
		
		if ($displayDefinition->getLabel() === null) {
			$displayDefinition->setLabel($this->getEiProp()->getLabelLstr()->t($n2nLocale));
		}
		
		if ($displayDefinition->getHelpText() === null
				&& null !== ($helpTextLstr = $this->getEiProp()->getHelpTextLstr())) {
			$displayDefinition->setHelpText($helpTextLstr->t($n2nLocale));
		}
		
		return $displayDefinition;
	}
	
	function buildForkDisplayDefinition(GuiPropPath $forkedGuiPropPath, EiGuiFrame $eiGuiFrame, bool $defaultDisplayedRequired) {
		return $this->guiProp->getForkGuiDefinition()->getGuiPropWrapperByGuiPropPath($forkedGuiPropPath)
				->buildDisplayDefinition($eiGuiFrame, $defaultDisplayedRequired);
	}
	
	/**
	 * @return GuiPropPath[]
	 */
	function getForkedGuiPropPaths() {
		$forkGuiDefinition = $this->guiProp->getForkGuiDefinition();
		
		if ($forkGuiDefinition === null) {
			return [];
		}
		
		return $forkGuiDefinition->getGuiPropPaths();
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @throws UnresolvableGuiPropPathException
	 * @return \rocket\ei\manage\gui\GuiPropWrapper
	 */
	function getForkedGuiPropWrapper(GuiPropPath $guiPropPath) {
		if (null !== ($forkGuiDefinition = $this->guiProp->getForkGuiDefinition())) {
			return $forkGuiDefinition->getGuiPropWrapperByGuiPropPath($guiPropPath);
		}
		
		throw new UnresolvableGuiPropPathException('GuiProp ' . $guiPropPath . ' not found.');
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param array $fokredGuiPropPaths
	 * @return \rocket\ei\manage\gui\GuiPropSetup
	 */
	function buildGuiPropSetup(N2nContext $n2nContext, EiGuiFrame $eiGuiFrame, ?array $forkedGuiPropPaths) {
		return $this->guiProp->buildGuiPropSetup(new Eiu($n2nContext, $eiGuiFrame, $this->eiPropPath), $forkedGuiPropPaths);
	}
	
	/**
	 * @return EiProp
	 */
	function getEiProp() {
		return $this->guiDefinition->getEiMask()->getEiPropCollection()->getByPath($this->eiPropPath);
	}
	
	
}