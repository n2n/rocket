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

use rocket\impl\ei\component\prop\adapter\IndependentEiPropAdapter;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\impl\ei\component\prop\adapter\AdaptableEiPropConfigurator;
use rocket\ei\component\prop\GuiEiProp;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\impl\SimpleEiField;
use rocket\ei\manage\entry\impl\Readable;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\adapter\DisplaySettings;
use rocket\impl\ei\component\prop\adapter\StatelessDisplayable;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\StatelessDisplayElement;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\ObjectPropertyConfigurable;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\GuiPropFork;

class StringDisplayEiProp extends IndependentEiPropAdapter implements ObjectPropertyConfigurable, GuiEiProp, GuiProp, 
		FieldEiProp, Readable, StatelessDisplayable {
	private $accessProxy;
	private $displaySettings;
	
	public function __construct() {
		parent::__construct();
	
		$this->displaySettings = new DisplaySettings(ViewMode::read());
	}
	
	public function buildDisplayDefinition(Eiu $eiu): DisplayDefinition {
		return $this->displaySettings->toDisplayDefinition($this, $eiu->gui()->getViewMode());
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerDisplaySettings($this->displaySettings);
		$eiPropConfigurator->registerObjectPropertyConfigurable($this);
		return $eiPropConfigurator;
	}
	
	public function getPropertyName(): string {
		return $this->getObjectPropertyAccessProxy()->getPropertyName();
	}

	public function getObjectPropertyAccessProxy(bool $required = false) {
		if ($this->accessProxy === null && $required) {
			throw new IllegalStateException('No object property AccessProxy assigned to ' . $this);
		}
		
		return $this->accessProxy;
	}

	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		ArgUtils::assertTrue($objectPropertyAccessProxy !== null);
		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createSimple('string', true));
		$this->accessProxy = $objectPropertyAccessProxy;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::getGuiProp()
	 */
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::isEiField()
	 */
	public function isEiField(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::buildEiField($eiObject)
	 */
	public function buildEiField(Eiu $eiu) {
		return new SimpleEiField($eiu->entry()->getEiObject(), $this->accessProxy->getConstraint(), $this);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::buildEiFieldFork($eiObject, $eiField)
	 */
	public function buildEiFieldFork(\rocket\ei\manage\EiObject $eiObject, \rocket\ei\manage\entry\EiField $eiField = null) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::isEiEntryFilterable()
	 */
	public function isEiEntryFilterable(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FieldEiProp::createSecurityFilterProp($n2nContext)
	 */
	public function createSecurityFilterProp(\n2n\core\container\N2nContext $n2nContext): SecurityFilterProp {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			return $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this));
		}
		
		return $this->accessProxy->getValue($eiObject->getLiveObject());
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayLabel()
	 */
	public function getDisplayLabel(): string {
		return (string) $this->labelLstr;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField($eiu)
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField {
		return new StatelessDisplayElement($this, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::isStringRepresentable()
	 */
	public function isStringRepresentable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildIdentityString($eiObject, $n2nLocale)
	 */
	public function buildIdentityString(\rocket\ei\manage\EiObject $eiObject, \n2n\l10n\N2nLocale $n2nLocale): ?string {
		return $this->read($eiObject);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\StatelessDisplayable::getUiOutputLabel()
	 */
	public function getUiOutputLabel(\rocket\ei\util\Eiu $eiu) {
		return $this->getLabelLstr();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\StatelessDisplayable::getOutputHtmlContainerAttrs()
	 */
	public function getOutputHtmlContainerAttrs(\rocket\ei\util\Eiu $eiu) {
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\StatelessDisplayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(\n2n\impl\web\ui\view\html\HtmlView $view, \rocket\ei\util\Eiu $eiu) {
		return $view->getHtmlBuilder()->getEsc($eiu->field()->getValue());
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\StatelessDisplayable::getDisplayItemType()
	 */
	public function getDisplayItemType(): ?string {
		// TODO Auto-generated method stub
		
	}
}
