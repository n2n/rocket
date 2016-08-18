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
namespace rocket\spec\ei\manage\critmod\filter\impl\field;

use rocket\spec\ei\manage\critmod\SimpleComparatorConstraint;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\impl\model\EnumMag;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\web\dispatch\mag\Mag;
use n2n\web\dispatch\mag\impl\model\MagForm;

abstract class FilterFieldAdapter implements FilterField {
	const ATTR_OPERATOR_KEY = 'operator';
	const ATTR_VALUE_KEY = 'value';
	
	protected $criteriaProperty;
	protected $labelLstr;
	
	public function __construct(CriteriaProperty $criteriaProperty, $labelLstr) {
		$this->criteriaProperty = $criteriaProperty;
		$this->labelLstr = Lstr::create($labelLstr);
	}
	
	public function getLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}
	
	public function createMagDispatchable(Attributes $attributes): MagDispatchable {
		$magCollection = new MagCollection();
		$magCollection->addMag(new EnumMag(self::ATTR_OPERATOR_KEY, 'Operator', 
				$this->buildOperatorOptions($this->getOperators()), null, true));
		$magCollection->addMag($this->createValueMag(self::ATTR_VALUE_KEY, $attributes->get(self::ATTR_VALUE_KEY, false)));
		return new MagForm($magCollection);
	}
	
	public function buildAttributes(MagDispatchable $magDispatchable): Attributes {
		$magCollection = $magDispatchable->getMagCollection();
		$operator = $magCollection->getMagByPropertyName(self::ATTR_OPERATOR_KEY)->getValue();

		return new Attributes(array(self::ATTR_OPERATOR_KEY => $operator,
				self::ATTR_VALUE_KEY => $this->buildValue($operator, 
						$magCollection->getMagByPropertyName(self::ATTR_VALUE_KEY))));
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\critmod\filter\impl\field\FilterField::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint {
		return new SimpleComparatorConstraint($this->criteriaProperty,
				$attributes->getEnum(self::ATTR_OPERATOR_KEY, $this->getOperators()),
				$attributes->get(self::ATTR_VALUE_KEY));
	}
	
	protected function getOperators(): array {
		return array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL);
	}
	
	protected function buildOperatorOptions(array $operators) {
		return array_combine($operators, $operators);
	}
	
	protected abstract function createValueMag(string $propertyName, $value): Mag;
	
	protected function buildValue($operator, Mag $mag) {
		return $mag->getValue();
	}
}
