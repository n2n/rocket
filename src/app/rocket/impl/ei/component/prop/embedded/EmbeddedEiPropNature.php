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

use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\adapter\PropertyEiPropNatureAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\EmbeddedEntityProperty;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\entry\EiField;
use rocket\op\ei\manage\gui\DisplayDefinition;
use rocket\op\ei\manage\gui\GuiDefinition;
use rocket\op\ei\manage\gui\GuiFieldMap;
use rocket\op\ei\manage\gui\GuiPropSetup;
use rocket\op\ei\util\Eiu;
use n2n\reflection\ReflectionUtils;
use n2n\web\dispatch\mag\Mag;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use rocket\impl\ei\component\prop\adapter\config\EditAdapter;
use rocket\op\ei\manage\gui\field\GuiField;

use rocket\op\ei\manage\gui\GuiProp;
use n2n\util\ex\NotYetImplementedException;
use rocket\si\meta\SiStructureType;

class EmbeddedEiPropNature extends PropertyEiPropNatureAdapter {
	private $sed;
	
	/**
	 * @return EditAdapter
	 */
	private function getEditConfig() {
		return $this->sed ?? $this->sed = new EditAdapter();
	}
	
	function prepare() {
		$this->getConfigurator()->addAdaption($this->getEditConfig());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropNatureAdapter::setEntityProperty()
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropNatureAdapter::setPropertyAccessProxy()
	 */
	public function setPropertyAccessProxy(?AccessProxy $accessProxy) {
		ArgUtils::assertTrue($accessProxy !== null);
		
		$targetClass = $this->requireEntityProperty()->getEmbeddedEntityPropertyCollection()->getClass();
		$accessProxy->setConstraint(TypeConstraint::createSimple($targetClass,
				$accessProxy->getConstraint()->allowsNull()));
		
		parent::setPropertyAccessProxy($accessProxy);
	}
	

	/**
	 * @return boolean
	 */
	public function isMandatory() {
		return $this->isMandatory();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\EiPropNatureAdapter::isPropFork()
	 */
	public function isPropFork(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\EiPropNatureAdapter::getPropForkObject()
	 */
	public function getPropForkObject(object $object): object {
		return $this->getPropertyAccessProxy()->getValue($object) 
				?? ReflectionUtils::createObject($this->getEntityProperty(true)
						->getEmbeddedEntityPropertyCollection()->getClass());
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		if (!$this->isMandatory()) {
			return new EmbeddedGuiProp($this);
		}
		
		$eiu->engine()->onNewGuiEntry(function (Eiu $eiu) {
			$value = $eiu->entry()->getValue($this);
			
			if ($value !== null) return;
			
			$eiu->guiEntry()->onSave(function () use ($eiu) {
				$eiu->entry()->setValue($this, $eiu->entry()->fieldMap($this));
			});
		});
		
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\prop\FieldEiProp::buildEiField()
	 */
	public function buildEiField(Eiu $eiu): ?EiField {
		return new EmbeddedEiField($eiu, $this);
	}

}

class EmbeddedGuiProp implements GuiProp {
	private $eiProp;
	
	public function __construct(EmbeddedEiPropNature $eiProp) {
		$this->eiProp = $eiProp;
	}
	
// 	public function isStringRepresentable(): bool {
// 		return false;
// 	}

// 	public function getDisplayHelpTextLstr(): ?Lstr {
// 		return null;
// 	}

// 	public function getDisplayLabelLstr(): Lstr {
// 		return $this->eiProp->getLabelLstr();
// 	}

	public function buildDisplayDefinition($eiu): ?DisplayDefinition {
		return new DisplayDefinition(SiStructureType::ITEM, true);
	}

	public function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return new EmbeddedGuiField($eiu, $this->eiProp);
	}
	
	public function getForkGuiDefinition(): ?GuiDefinition {
		return null;
	}
	public function buildGuiPropSetup(Eiu $eiu, ?array $defPropPaths): ?GuiPropSetup {
		return null;
	}
}


class EmbeddedGuiField implements GuiField {
	private $eiu;
	private $embeddedEiProp;
	private $mag;
	
	public function __construct(Eiu $eiu, EmbeddedEiPropNature $embeddedEiProp) {
		$this->eiu = $eiu;
		$this->embeddedEiProp = $embeddedEiProp;
	}
	
	public function save() {
		if (!$this->mag->getValue()) {
			$this->eiu->field()->setValue(null);
			return;
		}
		
		$this->eiu->field()->setValue($this->eiu->entry()->fieldMap($this->embeddedEiProp));
	}

	public function isReadOnly(): bool {
		return false;
	}

	public function getMag(): Mag {
		if ($this->mag !== null) {
			return $this->mag;
		}
		
		$this->mag = new TogglerMag($this->embeddedEiProp->getLabelLstr(),
				$this->eiu->field()->getValue() !== null);
		
		$this->eiu->guiEntry()->whenReady(function () {
			$this->mag->setOnAssociatedMagWrappers($this->eiu->guiEntry()
					->getSubMagWrappers($this->embeddedEiProp, true));
		});
		
		return $this->mag;
	}

	function createOutEifGuiField(Eiu $eiu): EifGuiField {
		return null;
	}

	public function isMandatory(): bool {
		return false;
	}
	
	public function getSiField(): SiField {
		throw new NotYetImplementedException();
	}
	
	public function getContextSiFields(): array {
		return [];
	}

	public function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}





	
}
