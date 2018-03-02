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
use rocket\impl\ei\component\prop\adapter\AdaptableEiPropConfigurator;
use rocket\spec\ei\component\prop\GuiEiProp;
use rocket\spec\ei\manage\gui\GuiProp;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\adapter\StatelessDisplayable;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;

class TypeEiProp extends DisplayableEiPropAdapter implements StatelessDisplayable, GuiEiProp, GuiProp {
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		return $this->getDisplaySettings()->toDisplayDefinition($this, $eiu->gui()->getViewMode());
	}

	public function createOutputUiComponent(HtmlView $view, Eiu $eiu) {
		$eiMask = $eiu->frame()->getEiFrame()->getContextEiEngine()->getEiMask()->determineEiMask(
				$eiu->entry()->getEiEntry()->getEiType());
		return $view->getHtmlBuilder()->getEsc($eiMask->getLabelLstr()->t($view->getN2nLocale()));
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$configurator = new AdaptableEiPropConfigurator($this);
		$configurator->registerDisplaySettings($this->getDisplaySettings());
		return $configurator;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\GuiProp::isStringRepresentable()
	 */
	public function isStringRepresentable(): bool {
		return true;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\GuiProp::buildIdentityString()
	 */
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): ?string {
		return $this->getEiMask()->determineEiMask($this->getEiType()->determineAdequateEiType(
				new \ReflectionClass($eiObject->getLiveObject())));
		
	}
}
