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

use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\si\content\SiField;
use rocket\si\content\impl\SiFields;

class TypeEiProp extends DisplayableEiPropAdapter implements StatelessGuiFieldDisplayable {
	
	protected function prepare() {
	}
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
// 		$eiu->prop()->getLabel();
// 		$eiu->prop()->getHelpText();
		return $this->getDisplayConfig()->toDisplayDefinition($eiu->gui()->getViewMode(),
				$eiu->prop()->getLabel());
	}

	public function createOutSiField(Eiu $eiu): SiField {
		$eiuMask = $eiu->context()->mask($eiu->entry()->getEiEntry()->getEiType());
		$iconType = $eiuMask->getIconType();
		$label = $eiuMask->getLabel();
		
		if (null === $iconType) {
			return SiFields::stringOut($label);
		}
		
		return SiFields::stringOut((string) new HtmlSnippet(
				new HtmlElement('i', array('class' => $iconType), ''),
				' ',
				new HtmlElement('span', null, $label)));
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
		if (!$eiMask->getEiType()->equals($eiObject->getEiEntityObj()->getEiType())) {
			$eiMask = $eiObject->getEiEntityObj()->getEiType()->getEiMask();
		}
		
		return $eiMask;
		
	}
}
