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
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiFieldAssembly;
use rocket\ei\manage\gui\GuiFieldFork;
use rocket\ei\manage\gui\GuiFieldForkEditable;
use rocket\ei\manage\gui\GuiPropPath;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\FieldEiProp;
use n2n\reflection\ReflectionUtils;
use rocket\ei\manage\gui\GuiPropFork;
use rocket\ei\component\prop\GuiEiPropFork;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\mag\Mag;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use rocket\ei\manage\gui\EiFieldAbstraction;

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
	
	public function buildGuiPropFork(Eiu $eiu): ?GuiPropFork {
		return new EmbeddedGuiPropFork($this);
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		return new EmbeddedEiField($eiu, $this);
	}
}

class EmbeddedGuiPropFork implements GuiPropFork {
	private $eiProp;
	
	public function __construct(EmbeddedEiProp $eiProp) {
		$this->eiProp = $eiProp;
	}
	
	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork {
		return new EmbeddedGuiFieldFork($eiu, $this->eiProp);
	}

	public function getForkedGuiDefinition(): ?GuiDefinition {
		return null;
	}

	public function determineForkedEiObject(EiObject $eiObject): ?EiObject {
		throw new IllegalStateException();
	}

	public function determineEiFieldAbstraction(Eiu $eiu, GuiPropPath $guiPropPath): EiFieldAbstraction {
		throw new IllegalStateException();
	}
}


class EmbeddedGuiFieldFork implements GuiFieldFork, GuiFieldForkEditable {
	private $eiu;
	private $embeddedEiProp;
	
	public function __construct(Eiu $eiu, EmbeddedEiProp $embeddedEiProp) {
		$this->eiu = $eiu;
		$this->embeddedEiProp = $embeddedEiProp;
	}
	
	public function assembleGuiFieldFork(): ?GuiFieldForkEditable {
		return $this;
	}

	public function assembleGuiField(GuiPropPath $guiPropPath): ?GuiFieldAssembly {
		throw new IllegalStateException();
	}
	
	public function getForkMag(): Mag {
		$togglerMag = new TogglerMag($this->embeddedEiProp->getLabelLstr());
		
		$eiuEntryGui = $this->eiu->entryGui();
		
		if ($eiuEntryGui->whenReady(function () {
			
			$this->eiu->gui()->forkedGuiPropPaths($this->embeddedEiProp);
			$togglerMag->setOnAssociatedMagWrappers();
		}));
		
	}

	public function isForkMandatory(): bool {
	}

	public function save() {
	}

	public function getInheritForkMagAssemblies(): array {
	}


	
}
