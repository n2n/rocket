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
namespace rocket\impl\ei\component\prop\string;

use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\component\prop\FieldEiProp;
use rocket\impl\ei\component\prop\adapter\entry\SimpleEiField;
use rocket\impl\ei\component\prop\adapter\entry\Readable;
use rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldProxy;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\impl\ei\component\prop\adapter\config\ObjectPropertyConfigurable;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiField;
use rocket\core\model\Rocket;
use n2n\l10n\Lstr;
use rocket\ei\manage\entry\EiField;
use n2n\util\StringUtils;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;
use rocket\impl\ei\component\prop\adapter\PropertyDisplayableEiPropAdapter;

class StringDisplayEiProp extends PropertyDisplayableEiPropAdapter implements ObjectPropertyConfigurable, 
		FieldEiProp, Readable, StatelessGuiFieldDisplayable {
	
	function prepare() {
		$this->getDisplayConfig()->setCompatibleViewModes(ViewMode::read());
	}
	
	function isEntityPropertyRequired() {
		return false;
	}

	function setObjectPropertyAccessProxy(?AccessProxy $objectPropertyAccessProxy) {
		ArgUtils::assertTrue($objectPropertyAccessProxy !== null);
		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createSimple('string', true));
		parent::setObjectPropertyAccessProxy($objectPropertyAccessProxy);
	}
	
	function getDisplayDefinition(): ?DisplayDefinition {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayLabelLstr()
	 */
	function getDisplayLabelLstr(): Lstr {
		return $this->getLabelLstr()->t($n2nLocale);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayHelpTextLstr()
	 */
	function getDisplayHelpTextLstr(): ?Lstr {
		$helpText = $this->displayConfig->getHelpText();
		if ($helpText === null) {
			return null;
		}
		
		return Rocket::createLstr($helpText, $this->getEiMask()->getModuleNamespace())->t($n2nLocale);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::getGuiProp()
	 */
	function buildGuiProp(Eiu $eiu): ?GuiProp {
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::isEiField()
	 */
	function isEiField(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::buildEiField($eiObject)
	 */
	function buildEiField(Eiu $eiu): ?EiField {
		return new SimpleEiField($eiu, $this->accessProxy->getConstraint(), $this);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::buildEiFieldFork($eiObject, $eiField)
	 */
	function buildEiFieldFork(\rocket\ei\manage\EiObject $eiObject, \rocket\ei\manage\entry\EiField $eiField = null) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::isEiEntryFilterable()
	 */
	function isEiEntryFilterable(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::createSecurityFilterProp($n2nContext)
	 */
	function createSecurityFilterProp(\n2n\core\container\N2nContext $n2nContext): SecurityFilterProp {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\Readable::read()
	 */
	function read(Eiu $eiu) {
		return $eiu->entry()->readNativValue($this);
		
// 		if ($eiObject->isDraft()) {
// 			return $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this));
// 		}
		
// 		return $this->accessProxy->getValue($eiObject->getLiveObject());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField($eiu)
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return new GuiFieldProxy($this, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::isStringRepresentable()
	 */
	function isStringRepresentable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildIdentityString($eiObject, $n2nLocale)
	 */
	function buildIdentityString(Eiu $eiu, \n2n\l10n\N2nLocale $n2nLocale): ?string {
		return StringUtils::strOf($eiu->object()->readNativValue($this), true);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable::getUiOutputLabel()
	 */
	function getUiOutputLabel(\rocket\ei\util\Eiu $eiu) {
		return $this->getLabelLstr();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable::getHtmlContainerAttrs()
	 */
	function getHtmlContainerAttrs(\rocket\ei\util\Eiu $eiu) {
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldDisplayable::createUiComponent()
	 */
	function createOutSiField(Eiu $eiu): SiField {
		return $view->getHtmlBuilder()->getEsc($eiu->field()->getValue());
	}

	
	


}
