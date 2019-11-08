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
namespace rocket\impl\ei\component\prop\meta;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\gui\GuiProp;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use n2n\impl\web\ui\view\html\HtmlElement;

class TypeEiProp extends DisplayableEiPropAdapter implements StatelessGuiFieldDisplayable, GuiEiProp, GuiProp {
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		return $this->getDisplayConfig()->toDisplayDefinition($this, $eiu->gui()->getViewMode());
	}

	public function createUiComponent(HtmlView $view, Eiu $eiu) {
		$eiuMask = $eiu->context()->mask($eiu->entry()->getEiEntry()->getEiType());
		$iconType = $eiuMask->getIconType();
		$label = $eiuMask->getLabel();
		
		if (null === $iconType) return $label;
		
		return new HtmlSnippet(
				new HtmlElement('i', array('class' => $iconType), ''),
				' ',
				new HtmlElement('span', null, $label));
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$configurator = new AdaptableEiPropConfigurator($this);
		$configurator->registerDisplayConfig($this->getDisplayConfig());
		return $configurator;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\gui\GuiProp::isStringRepresentable()
	 */
	public function isStringRepresentable(): bool {
		return true;
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\gui\GuiProp::buildIdentityString()
	 */
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$eiMask = $this->getEiMask();
		$eiObject = $eiu->object()->getEiObject();
		if (!$eiMask->getEiType()->equals($eiObject->getEiEntityObj()->getEiType())) {
			$eiMask = $eiObject->getEiEntityObj()->getEiType()->getEiMask();
		}
		
		return $eiMask;
		
	}
}
