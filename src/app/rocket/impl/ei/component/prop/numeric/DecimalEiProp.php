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
namespace rocket\impl\ei\component\prop\numeric;

use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\numeric\conf\DecimalEiPropConfigurator;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\dispatch\mag\UiOutfitter;
use n2n\impl\persistence\orm\property\FloatEntityProperty;

class DecimalEiProp extends NumericEiPropAdapter {
	protected $decimalPlaces = 0;
	protected $prefix;

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropAdapter::createEiPropConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		return new DecimalEiPropConfigurator($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropAdapter::setEntityProperty()
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty
				|| $entityProperty instanceof FloatEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropAdapter::setObjectPropertyAccessProxy()
	 */
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('float',
				$propertyAccessProxy->getBaseConstraint()->allowsNull(), true));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	/**
	 * @return int
	 */
	public function getDecimalPlaces() {
		return $this->decimalPlaces;
	}
	
	/**
	 * @param int $decimalPlaces
	 */
	public function setDecimalPlaces(int $decimalPlaces) {
		$this->decimalPlaces = $decimalPlaces;
	}
	
	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	
	/**
	 * @param string $prefix
	 */
	public function setPrefix(string $prefix = null) {
		$this->prefix = $prefix;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\gui\StatelessGuiFieldEditable::createMag($eiu)
	 */
	public function createMag(Eiu $eiu): Mag {
		$numericMag = new EiDecimalMag($this->getLabelLstr(), null,
				$this->isMandatory($eiu), $this->getMinValue(), $this->getMaxValue(), 
				$this->getDecimalPlaces(), array('placeholder' => $this->getLabelLstr()));
		$numericMag->setInputPrefix($this->prefix);
		return $numericMag;
	}
}


class EiDecimalMag extends NumericMag {
	private $inputPrefix;
	
	/**
	 * @return string
	 */
	public function getInputPrefix() {
		return $this->inputPrefix;
	}
	
	/**
	 * @param string $inputPrefix
	 */
	public function setInputPrefix(string $inputPrefix = null) {
		$this->inputPrefix = $inputPrefix;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\impl\web\dispatch\mag\model\NumericMag::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		$input = parent::createUiField($propertyPath, $view, $uiOutfitter);
	
		if ($this->inputPrefix === null) return $input;
		
		return new HtmlElement('div', array('class' => 'input-group'), array(
				new HtmlElement('span', array('class' => 'input-group-addon'), $this->inputPrefix),
				$input));
	}
}
