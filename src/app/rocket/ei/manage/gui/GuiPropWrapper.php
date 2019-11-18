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
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\manage\gui\field\GuiPropPath;

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
	 * @param EiGui $eiGui
	 * @return DisplayDefinition|null
	 */
	function buildDisplayDefinition(EiGui $eiGui, bool $defaultDisplayedRequired) {
		$displayDefinition = $this->guiProp->buildDisplayDefinition(new Eiu($eiGui, $this->eiPropPath));
		
		if ($displayDefinition === null || ($defaultDisplayedRequired && !$displayDefinition->isDefaultDisplayed())) {
			return null;
		}
		
		if ($displayDefinition->getOverwriteLabel() !== null && $displayDefinition->getOverwriteLabel() !== null) {
			return $displayDefinition;
		}
		
		$n2nLocale = $eiGui->getEiFrame()->getN2nContext()->getN2nLocale();
		
		if ($displayDefinition->getLabel() === null) {
			$displayDefinition->setLabel($this->getEiProp()->getLabelLstr()->t($n2nLocale));
		}
		
		if ($displayDefinition->getHelpText() === null
				&& null !== ($helpTextLstr = $this->getEiProp()->getHelpTextLstr())) {
			$displayDefinition->setHelpText($helpTextLstr->t($n2nLocale));
		}
		
		return $displayDefinition;
	}
	
	function buildForkDisplayDefinition(GuiPropPath $forkedGuiPropPath, EiGui $eiGui, bool $defaultDisplayedRequired) {
		return $this->guiProp->getForkGuiDefinition()->getGuiPropWrapperByGuiPropPath($forkedGuiPropPath)
				->buildDisplayDefinition($eiGui, $defaultDisplayedRequired);
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
	 * @param EiGui $eiGui
	 * @param array $fokredGuiPropPaths
	 * @return \rocket\ei\manage\gui\GuiPropSetup
	 */
	function buildGuiPropSetup(EiGui $eiGui, ?array $forkedGuiPropPaths) {
		return $this->guiProp->buildGuiPropSetup(new Eiu($eiGui, $this->eiPropPath), $forkedGuiPropPaths);
	}
	
	/**
	 * @return EiProp
	 */
	function getEiProp() {
		return $this->guiDefinition->getEiMask()->getEiPropCollection()->getByPath($this->eiPropPath);
	}
	
	
}