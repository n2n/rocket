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
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\web\dispatch\mag\Mag;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\ScalarEiProp;
use rocket\spec\ei\manage\generic\CommonScalarEiProperty;

class IntegerEiProp extends NumericEiPropAdapter implements ScalarEiProp {
	const INT_SIGNED_MIN = -2147483648;
	const INT_SIGNED_MAX = 2147483647;
	
	public function __construct() {
		parent::__construct();
		
		$this->minValue = self::INT_SIGNED_MIN;
		$this->maxValue = self::INT_SIGNED_MAX;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\ScalarEiProp::buildScalarValue()
	 */
	public function getScalarEiProperty() {
		return new CommonScalarEiProperty($this, null, function ($value) {
			ArgUtils::valScalar($value, true);
			if ($value === null) return null;
			return (int) $value;
		});
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
	
	public function createMag(Eiu $eiu): Mag {
		return new NumericMag($this->getLabelLstr(), null,
				$this->isMandatory($eiu), $this->getMinValue(), $this->getMaxValue(), 
				0, array('placeholder' => $this->getLabelLstr()));
	}
}
