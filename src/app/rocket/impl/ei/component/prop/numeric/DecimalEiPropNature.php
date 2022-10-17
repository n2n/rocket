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
use rocket\ei\util\Eiu;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\dispatch\mag\UiOutfitter;
use n2n\impl\persistence\orm\property\FloatEntityProperty;
use rocket\impl\ei\component\prop\numeric\conf\DecimalConfig;
use rocket\si\content\SiField;
use rocket\ei\util\factory\EifGuiField;
use rocket\si\content\impl\SiFields;
use n2n\validation\validator\impl\Validators;
use rocket\ei\util\factory\EifField;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\type\TypeConstraints;

class DecimalEiPropNature extends NumericEiPropNatureAdapter {
    private int $decimalPlaces = 2;

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::float(true)));
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
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropNatureAdapter::setEntityProperty()
//	 */
//	public function setEntityProperty(?EntityProperty $entityProperty) {
//		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty
//				|| $entityProperty instanceof FloatEntityProperty);
//		$this->entityProperty = $entityProperty;
//	}
	
	function createEifField(Eiu $eiu): EifField {
		return parent::createEifField($eiu)
				->val(Validators::min($this->getMinValue() ?? PHP_FLOAT_MIN),
						Validators::max($this->getMaxValue() ?? PHP_FLOAT_MAX));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropNatureAdapter::setPropertyAccessProxy()
	 */
	public function setPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('float',
				$propertyAccessProxy->getBaseConstraint()->allowsNull(), true));
		$this->propertyAccessProxy = $propertyAccessProxy;
	}

	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		
		$step = 1 / pow(10, $this->getDecimalPlaces());
		$siField = SiFields::numberIn($eiu->field()->getValue())
				->setMandatory($this->isMandatory())
				->setMin($this->getMinValue())
				->setMax($this->getMaxValue())
				->setStep($step)
				->setArrowStep($step)
				->setFixed(true)
				->setPrefixAddons($this->getPrefixSiCrumbGroups())
				->setSuffixAddons($this->getSuffixSiCrumbGroups())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($siField, $eiu) {
					$eiu->field()->setValue($siField->getValue());
				});
	}
	public function saveSiField(SiField $siField, Eiu $eiu) {
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
