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
namespace rocket\impl\ei\component\prop\date;

use rocket\impl\ei\component\prop\adapter\ObjectPropertyEiPropAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\EmbeddedEntityProperty;
use n2n\reflection\ArgUtils;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\FieldEiProp;

class EmbeddedEiProp extends ObjectPropertyEiPropAdapter implements GuiEiProp, FieldEiProp {
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
	}
	
	public function buildEiField(Eiu $eiu) {
	}

	public function buildEiFieldFork(EiObject $eiObject, EiField $eiField = null) {
	}

}