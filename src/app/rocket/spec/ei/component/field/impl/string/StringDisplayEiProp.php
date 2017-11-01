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
namespace rocket\spec\ei\component\field\impl\string;

use rocket\spec\ei\component\field\impl\adapter\IndependentEiPropAdapter;
use rocket\spec\ei\component\field\ObjectPropertyEiProp;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\component\field\impl\adapter\AdaptableEiPropConfigurator;
use rocket\spec\ei\component\field\GuiEiProp;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\FieldEiProp;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\impl\SimpleEiField;
use rocket\spec\ei\manage\mapping\impl\Readable;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable;
use rocket\spec\ei\manage\gui\GuiProp;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\impl\adapter\StatelessDisplayElement;
use rocket\spec\ei\manage\mapping\EiField;
use rocket\spec\ei\manage\critmod\filter\EiEntryFilterField;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;
use rocket\spec\ei\component\field\impl\adapter\ConfObjectPropertyEiProp;
use n2n\reflection\ArgUtils;

class StringDisplayEiProp extends IndependentEiPropAdapter implements ConfObjectPropertyEiProp, GuiEiProp, GuiProp, 
		FieldEiProp, Readable, StatelessDisplayable {
	private $accessProxy;
	private $displayDefinition;
	
	public function __construct() {
		parent::__construct();
	
		$this->displayDefinition = new DisplayDefinition(DisplayDefinition::READ_VIEW_MODES);
	}
	
	public function getDisplayDefinition(): DisplayDefinition {
		return $this->displayDefinition;
	}

	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerDisplayDefinition($this->displayDefinition);
		$eiPropConfigurator->registerConfObjectPropertyEiProp($this);
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
	 * @see \rocket\spec\ei\component\field\GuiEiProp::getGuiProp()
	 */
	public function getGuiProp() {
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\GuiEiProp::getGuiPropFork()
	 */
	public function getGuiPropFork() {
		return null;
		
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\FieldEiProp::isEiField()
	 */
	public function isEiField(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\FieldEiProp::buildEiField($eiObject)
	 */
	public function buildEiField(Eiu $eiu) {
		return new SimpleEiField($eiu->entry()->getEiObject(), $this->accessProxy->getConstraint(), $this);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\FieldEiProp::buildEiFieldFork($eiObject, $eiField)
	 */
	public function buildEiFieldFork(\rocket\spec\ei\manage\EiObject $eiObject, \rocket\spec\ei\manage\mapping\EiField $eiField = null) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\FieldEiProp::isEiEntryFilterable()
	 */
	public function isEiEntryFilterable(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\FieldEiProp::createEiEntryFilterField($n2nContext)
	 */
	public function createEiEntryFilterField(\n2n\core\container\N2nContext $n2nContext): EiEntryFilterField {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			return $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this));
		}
		
		return $this->accessProxy->getValue($eiObject->getLiveObject());
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiProp::getDisplayLabel()
	 */
	public function getDisplayLabel(): string {
		return (string) $this->labelLstr;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiProp::buildGuiField($eiu)
	 */
	public function buildGuiField(Eiu $eiu) {
		return new StatelessDisplayElement($this, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiProp::isStringRepresentable()
	 */
	public function isStringRepresentable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiProp::buildIdentityString($eiObject, $n2nLocale)
	 */
	public function buildIdentityString(\rocket\spec\ei\manage\EiObject $eiObject, \n2n\l10n\N2nLocale $n2nLocale) {
		return $this->read($eiObject);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable::getUiOutputLabel()
	 */
	public function getUiOutputLabel(\rocket\spec\ei\manage\util\model\Eiu $eiu) {
		return $this->getLabelLstr();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable::getOutputHtmlContainerAttrs()
	 */
	public function getOutputHtmlContainerAttrs(\rocket\spec\ei\manage\util\model\Eiu $eiu) {
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(\n2n\impl\web\ui\view\html\HtmlView $view, \rocket\spec\ei\manage\util\model\Eiu $eiu) {
		return $view->getHtmlBuilder()->getEsc($eiu->field()->getValue());
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable::getGroupType()
	 */
	public function getGroupType() {
		// TODO Auto-generated method stub
		
	}
}
