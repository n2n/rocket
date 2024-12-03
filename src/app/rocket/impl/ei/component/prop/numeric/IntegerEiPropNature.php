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

use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\generic\CommonScalarEiProperty;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\util\Eiu;
use rocket\ui\si\content\impl\SiFields;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\validation\validator\impl\Validators;
use rocket\op\ei\util\factory\EifField;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\util\type\TypeConstraints;
use rocket\ui\gui\field\GuiField;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\gui\field\BackableGuiField;

class IntegerEiPropNature extends NumericEiPropNatureAdapter {
	const INT_SIGNED_MIN = -2147483648;
	const INT_SIGNED_MAX = 2147483647;

	function __construct(PropertyAccessProxy $propertyAccessProxy) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::int(true)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\prop\ScalarEiProp::buildScalarValue()
	 */
	function buildScalarEiProperty(Eiu $eiu): ?ScalarEiProperty {
		return new CommonScalarEiProperty($eiu->prop()->getPath(), $this->getLabelLstr(), function ($value) {
			ArgUtils::valScalar($value, true);
			if ($value === null) return null;
			return (int) $value;
		});
	}
	
//	function setEntityProperty(?EntityProperty $entityProperty) {
//		ArgUtils::assertTrue($entityProperty instanceof IntEntityProperty
//				|| $entityProperty instanceof ScalarEntityProperty);
//		$this->entityProperty = $entityProperty;
//	}
//
//	function setPropertyAccessProxy(?AccessProxy $propertyAccessProxy = null) {
//		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('int',
//				$propertyAccessProxy->getBaseConstraint()->allowsNull(), true));
//
//		$this->propertyAccessProxy = $propertyAccessProxy;
//	}
	
	function createEifField(Eiu $eiu): EifField {
		return parent::createEifField($eiu)
				->val(Validators::min($this->getMinValue() ?? self::INT_SIGNED_MIN),
						Validators::max($this->getMaxValue() ?? self::INT_SIGNED_MAX));
	}
	

	function buildInGuiField(Eiu $eiu): ?BackableGuiField {
		return GuiFields::numberIn(mandatory: $this->isMandatory(), min: $this->getMinValue(), max: $this->getMaxValue());
	}
}
