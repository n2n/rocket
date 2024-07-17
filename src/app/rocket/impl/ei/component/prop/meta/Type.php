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

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\DisplayDefinition;
use rocket\impl\ei\component\prop\adapter\DisplayConfigTrait;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\op\ei\util\factory\EifGuiField;
use rocket\ui\si\content\impl\meta\SiCrumb;

class Type extends DisplayConfigTrait {
	
	protected function prepare() {
	}
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
// 		$eiu->prop()->getLabel();
// 		$eiu->prop()->getHelpText();
		return $this->getDisplayConfig()->toDisplayDefinition($eiu->guiMaskDeclaration()->getViewMode(),
				$eiu->prop()->getLabel());
	}

	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		$eiuMask = $eiu->context()->mask($eiu->entry()->getEiEntry()->getEiType());
		$iconType = $eiuMask->getIconType();
		$label = $eiuMask->getLabel();
		
		if (null === $iconType) {
			return SiFields::stringOut($label);
		}
		
		return $eiu->factory()->newGuiField(SiFields::crumbOut(SiCrumb::createIcon($iconType), 
				SiCrumb::createLabel($label)));
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			$eiMask = $this->getEiMask();
			$eiObject = $eiu->object()->getEiObject();
			if (!$eiMask->getEiType()->equals($eiObject->getEiEntityObj()->getEiType())) {
				$eiMask = $eiObject->getEiEntityObj()->getEiType()->getEiMask();
			}
			
			return $eiMask;
		})->toIdNameProp();
	}

}
