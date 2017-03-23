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

use rocket\spec\ei\component\field\impl\adapter\IndependentEiFieldAdapter;
use rocket\spec\ei\component\field\ObjectPropertyEiField;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use rocket\spec\ei\component\field\GuiEiField;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\MappableEiField;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\impl\SimpleMappable;
use rocket\spec\ei\manage\mapping\impl\Readable;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\component\field\impl\adapter\StatelessDisplayable;
use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\impl\adapter\StatelessDisplayElement;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\component\field\impl\adapter\ConfObjectPropertyEiField;
use n2n\reflection\ArgUtils;

class StringDisplayEiField extends IndependentEiFieldAdapter implements ConfObjectPropertyEiField, GuiEiField, GuiField, 
		MappableEiField, Readable, StatelessDisplayable {
	private $accessProxy;
	private $displayDefinition;
	
	public function __construct() {
		parent::__construct();
	
		$this->displayDefinition = new DisplayDefinition(DisplayDefinition::READ_VIEW_MODES);
	}
	
	public function getDisplayDefinition(): DisplayDefinition {
		return $this->displayDefinition;
	}

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		$eiFieldConfigurator = parent::createEiFieldConfigurator();
		IllegalStateException::assertTrue($eiFieldConfigurator instanceof AdaptableEiFieldConfigurator);
		$eiFieldConfigurator->registerDisplayDefinition($this->displayDefinition);
		$eiFieldConfigurator->registerConfObjectPropertyEiField($this);
		return $eiFieldConfigurator;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\ObjectPropertyEiField::getPropertyName()
	 */
	public function getPropertyName(): string {
		return $this->getObjectPropertyAccessProxy()->getPropertyName();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\ObjectPropertyEiField::getPropertyAccessProxy()
	 */
	public function getObjectPropertyAccessProxy(bool $required = false) {
		if ($this->accessProxy === null && $required) {
			throw new IllegalStateException('No object property AccessProxy assigned to ' . $this);
		}
		
		return $this->accessProxy;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\ObjectPropertyEiField::setPropertyAccessProxy($propertyAccessProxy)
	 */
	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		ArgUtils::assertTrue($objectPropertyAccessProxy !== null);
		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createSimple('string', true));
		$this->accessProxy = $objectPropertyAccessProxy;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\GuiEiField::getGuiField()
	 */
	public function getGuiField() {
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\GuiEiField::getGuiFieldFork()
	 */
	public function getGuiFieldFork() {
		return null;
		
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\MappableEiField::isMappable()
	 */
	public function isMappable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\MappableEiField::buildMappable($eiObject)
	 */
	public function buildMappable(Eiu $eiu) {
		return new SimpleMappable($eiu->entry()->getEiEntry(), $this->accessProxy->getConstraint(), $this);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\MappableEiField::buildMappableFork($eiObject, $mappable)
	 */
	public function buildMappableFork(\rocket\spec\ei\manage\EiObject $eiObject, \rocket\spec\ei\manage\mapping\Mappable $mappable = null) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\MappableEiField::isEiMappingFilterable()
	 */
	public function isEiMappingFilterable(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\MappableEiField::createEiMappingFilterField($n2nContext)
	 */
	public function createEiMappingFilterField(\n2n\core\container\N2nContext $n2nContext): EiMappingFilterField {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			return $eiObject->getDraftValueMap()->getValue(EiFieldPath::from($this));
		}
		
		return $this->accessProxy->getValue($eiObject->getLiveObject());
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::getDisplayLabel()
	 */
	public function getDisplayLabel(): string {
		return (string) $this->labelLstr;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement($eiu)
	 */
	public function buildGuiElement(Eiu $eiu) {
		return new StatelessDisplayElement($this, $eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::isStringRepresentable()
	 */
	public function isStringRepresentable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildIdentityString($eiObject, $n2nLocale)
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
}
