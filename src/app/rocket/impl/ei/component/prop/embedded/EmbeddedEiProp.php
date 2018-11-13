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
namespace rocket\impl\ei\component\prop\embedded;

use rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\EmbeddedEntityProperty;
use n2n\reflection\ArgUtils;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiFieldAssembly;
use rocket\ei\manage\gui\GuiFieldFork;
use rocket\ei\manage\gui\GuiFieldForkEditable;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\FieldEiProp;
use n2n\reflection\ReflectionUtils;
use rocket\ei\manage\gui\GuiPropFork;
use rocket\ei\component\prop\GuiEiPropFork;

class EmbeddedEiProp extends PropertyEiPropAdapter implements GuiEiPropFork, FieldEiProp {
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	public function isPropFork(): bool {
		return true;
	}
	
	public function getPropForkObject(object $object): object {
		return $this->getObjectPropertyAccessProxy()->getValue($object) 
				?? ReflectionUtils::createObject($this->getEntityProperty(true)
						->getEmbeddedEntityPropertyCollection()->getClass());
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return null;
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		return new EmbeddedEiField($eiu, $this);
	}
	
	public function buildGuiPropFork(Eiu $eiu): ?GuiPropFork {
// 		return new EmbeddedGuiPropFork();
	}
}

class EmbeddedGuiPropFork implements GuiPropFork {
	
	
	public function __construct() {
	}
	
	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork {
	}

	public function getForkedGuiDefinition(): GuiDefinition {
	}

	public function determineForkedEiObject(EiObject $eiObject): ?EiObject {
	}

	public function determineEiFieldWrapper(EiEntry $eiEntry, GuiIdPath $guiIdPath) {
	}
}


class EmbeddedGuiFieldFork implements GuiFieldFork {
	
	public function assembleGuiFieldFork(): ?GuiFieldForkEditable {
	}

	public function assembleGuiField(GuiIdPath $guiIdPath): ?GuiFieldAssembly {
	}

	
}
