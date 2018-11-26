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
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiFieldAssembly;
use rocket\ei\manage\gui\GuiFieldFork;
use rocket\ei\manage\gui\GuiFieldForkEditable;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\GuiPropPath;
use rocket\ei\util\Eiu;
use rocket\ei\component\prop\FieldEiProp;
use n2n\reflection\ReflectionUtils;
use rocket\ei\manage\gui\GuiPropFork;
use rocket\ei\component\prop\GuiEiPropFork;
use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\mag\Mag;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\Lstr;
use n2n\l10n\N2nLocale;
use rocket\ei\manage\gui\EiFieldAbstraction;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\config\StandardEditDefinition;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\gui\GuiFieldEditable;
use rocket\ei\manage\gui\ui\DisplayItem;

class EmbeddedEiProp extends PropertyEiPropAdapter implements GuiEiProp, FieldEiProp {
	private $sed;
	
	/**
	 * @return \rocket\impl\ei\component\prop\adapter\config\StandardEditDefinition
	 */
	private function getStandardEditDefinition() {
		return $this->sed ?? $this->sed = new StandardEditDefinition();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::setEntityProperty()
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof EmbeddedEntityProperty);
		
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::setObjectPropertyAccessProxy()
	 */
	public function setObjectPropertyAccessProxy(?AccessProxy $accessProxy) {
		ArgUtils::assertTrue($accessProxy !== null);
		
		$targetClass = $this->requireEntityProperty()->getEmbeddedEntityPropertyCollection()->getClass();
		$accessProxy->setConstraint(TypeConstraint::createSimple($targetClass,
				$accessProxy->getConstraint()->allowsNull()));
		
		parent::setObjectPropertyAccessProxy($accessProxy);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::createEiPropConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		$eepc = new EmbeddedEiPropConfigurator($this);
		$eepc->registerStandardEditDefinition($this->getStandardEditDefinition());
		return $eepc;
	}
	
	/**
	 * @return boolean
	 */
	public function isMandatory() {
		return $this->getStandardEditDefinition()->isMandatory();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\EiPropAdapter::isPropFork()
	 */
	public function isPropFork(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\EiPropAdapter::getPropForkObject()
	 */
	public function getPropForkObject(object $object): object {
		return $this->getObjectPropertyAccessProxy()->getValue($object) 
				?? ReflectionUtils::createObject($this->getEntityProperty(true)
						->getEmbeddedEntityPropertyCollection()->getClass());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiPropFork::buildGuiPropFork()
	 */
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		if ($this->isMandatory()) return null;
		
		return new EmbeddedGuiProp($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::buildEiField()
	 */
	public function buildEiField(Eiu $eiu): ?EiField {
		return new EmbeddedEiField($eiu, $this);
	}
}

class EmbeddedGuiProp implements GuiProp {
	private $eiProp;
	
	public function __construct(EmbeddedEiProp $eiProp) {
		$this->eiProp = $eiProp;
	}
	
	public function isStringRepresentable(): bool {
		return false;
	}

	public function getDisplayHelpTextLstr(): ?Lstr {
		return null;
	}

	public function getDisplayLabelLstr(): Lstr {
		return $this->eiProp->getLabelLstr();
	}

	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		return new DisplayDefinition(DisplayItem::TYPE_ITEM, true);
	}

	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		return null;
	}

	public function buildGuiField(Eiu $eiu): ?GuiField {
		return new EmbeddedGuiField($eiu, $this->eiProp);
	}

}


class EmbeddedGuiField implements GuiField, GuiFieldEditable {
	private $eiu;
	private $embeddedEiProp;
	private $mag;
	
	public function __construct(Eiu $eiu, EmbeddedEiProp $embeddedEiProp) {
		$this->eiu = $eiu;
		$this->embeddedEiProp = $embeddedEiProp;
	}
	
	public function getEditable(): GuiFieldEditable {
		return $this;
	}

	public function save() {
	}

	public function getOutputHtmlContainerAttrs(): array {
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
		
// 		$this->eiu->entryGui()->getMagWrapper($eiu->getGuiPropPath())
		
		return $this->mag;
	}

	public function createOutputUiComponent(HtmlView $view) {
		return null;
	}

	public function getDisplayItemType(): ?string {
		return null;
	}

	public function isMandatory(): bool {
		return false;
	}



	
}
