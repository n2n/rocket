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
namespace rocket\spec\ei\component\field\impl\numeric;

use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\reflection\ArgUtils;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;
use rocket\spec\ei\component\field\impl\numeric\conf\DecimalEiFieldConfigurator;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlElement;

class DecimalEiField extends NumericEiFieldAdapter {
	protected $decimalPlaces = 0;
	protected $prefix;

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new DecimalEiFieldConfigurator($this);
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function getDecimalPlaces() {
		return $this->decimalPlaces;
	}
	
	public function setDecimalPlaces($decimalPlaces) {
		$this->decimalPlaces = (int) $decimalPlaces;
	}
	
	public function getPrefix() {
		return $this->prefix;
	}
	
	public function setPrefix(string $prefix = null) {
		$this->prefix = $prefix;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessEditable::createMag($propertyName, $entrySourceInfo)
	 */
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		$numericMag = new EiDecimalMag($propertyName, $this->getLabelLstr(), null,
				$this->isMandatory($entrySourceInfo), $this->getMinValue(), $this->getMaxValue(), 
				$this->getDecimalPlaces(), array('placeholder' => $this->getLabelLstr()));
		$numericMag->setInputPrefix($this->prefix);
		return $numericMag;
	}
}


class EiDecimalMag extends NumericMag {
	private $inputPrefix;
	
	public function getInputPrefix() {
		return $this->inputPrefix;
	}
	
	public function setInputPrefix(string $inputPrefix = null) {
		$this->inputPrefix = $inputPrefix;
	}
	

	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		$input = parent::createUiField($propertyPath, $view);
	
		if ($this->inputPrefix === null) return $input;
		
		return new HtmlElement('div', array('class' => 'input-group'), array(
				new HtmlElement('span', array('class' => 'input-group-addon'), $this->inputPrefix),
				$input));
	}
}