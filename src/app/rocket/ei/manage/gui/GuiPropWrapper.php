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
	
	/**
	 * @return EiProp
	 */
	private function getEiProp() {
		return $this->guiDefinition->getEiMask()->getEiPropCollection()->getByPath($this->eiPropPath);
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @return GuiField|null
	 */
	function buildGuiField(EiGui $eiGui, EiEntryGui $eiEntryGui) {
		$readOnly = ViewMode::isReadOnly($eiGui->getViewMode()) 
				|| !$eiEntryGui->getEiEntry()->getEiEntryAccess()->isEiPropWritable($this->eiPropPath);
				
		$guiField = $this->guiProp->buildGuiField(new Eiu($eiGui, $eiEntryGui, $this->eiPropPath), $readOnly);
		
		if (!$readOnly || $guiField->getSiField()->isReadOnly()) {
			return $guiField;
		}
		
		throw new GuiException('GuiField of ' . $this->getEiProp() . ' must have a read-only SiField.');
	}
}