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

use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\web\dispatch\mag\impl\model\NumericMag;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\reflection\ArgUtils;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\impl\ScalarEntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\gui\FieldSourceInfo;

class DecimalEiField extends NumericEiFieldAdapter {
	protected $decimalPlaces = 0;
	
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
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\impl\adapter\StatelessEditable::createMag($propertyName, $entrySourceInfo)
	 */
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		return new NumericMag($propertyName, $this->getLabelLstr(), null,
				$this->isMandatory($entrySourceInfo), $this->getMinValue(), $this->getMaxValue(), 
				$this->getDecimalPlaces(), array('placeholder' => $this->getLabelLstr()));
	}
}
