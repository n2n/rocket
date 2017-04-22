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
namespace rocket\spec\ei\component\field\impl\meta;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\EiFrame;
use n2n\util\config\Attributes;
use rocket\spec\ei\component\field\impl\adapter\AdaptableEiPropConfigurator;
use rocket\spec\ei\component\field\impl\adapter\IndependentEiPropAdapter;
use rocket\spec\ei\component\field\GuiEiProp;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\gui\GuiField;

use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\field\impl\adapter\StatelessDisplayElement;
use rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;

class TypeEiProp extends IndependentEiPropAdapter implements StatelessDisplayable, GuiEiProp, GuiField {
	private $displayDefinition;
	
	public function __construct() {
		parent::__construct();
		
		$this->displayDefinition = new DisplayDefinition(DisplayDefinition::READ_VIEW_MODES);
	}
	
	public function getDisplayDefinition(): DisplayDefinition {
		return $this->displayDefinition;
	}
	
	public function createDisplayable(EiFrame $eiFrame, Attributes $maskAttributes) {
		return $this;
	}

	public function getDisplayLabel(): string {
		return $this->getLabelLstr();
	}
	
	public function getOutputHtmlContainerAttrs(Eiu $eiu) {
		return array();
	}
	
	public function getUiOutputLabel(Eiu $eiu) {
		return $this->getLabelLstr()->t($eiu->frame()->getN2nLocale());
	}
	
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu) {
		$eiMask = $eiu->frame()->getEiFrame()->getContextEiMask()->determineEiMask(
				$eiu->entry()->getEiMapping()->getEiType());
		return $view->getHtmlBuilder()->getEsc($eiMask->getLabelLstr()->t($view->getN2nLocale()));
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$configurator = new AdaptableEiPropConfigurator($this);
		$configurator->registerDisplayDefinition($this->displayDefinition);
		return $configurator;
	}
	
// 	/* (non-PHPdoc)
// 	 * @see \rocket\spec\ei\manage\gui\Displayable::getOutputHtmlContainerAttrs($eiFrame, $eiMapping, $maskAttributes)
// 	 */
// 	public function getOutputHtmlContainerAttrs(EntryModel $entryModel) {
// 		return array('class' => 'rocket-script-' . $this->eiType->getId() . ' rocket-field-' . $this->getId());
// 	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\GuiEiProp::getGuiField()
	 */
	public function getGuiField() {
		return $this;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\GuiEiProp::getGuiFieldFork()
	 */
	public function getGuiFieldFork() {
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement()
	 */
	public function buildGuiElement(Eiu $eiu) {
		return new StatelessDisplayElement($this, $eiu);
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\GuiField::isStringRepresentable()
	 */
	public function isStringRepresentable(): bool {
		return true;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildIdentityString()
	 */
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		return $this->getEiMask()->determineEiMask($this->getEiType()->determineAdequateEiType(
				new \ReflectionClass($eiObject->getLiveObject())));
		
	}
}
