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

use rocket\core\model\Rocket;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\config\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\spec\ei\manage\critmod\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\manage\mapping\MappableConstraint;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\l10n\MessageCode;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;

class BoolFilterField implements EiMappingFilterField {
	const ATTR_VALUE_KEY = 'value';
	const ATTR_VALUE_DEFAULT = false;
	
	protected $criteriaProperty;
	protected $labelLstr;
	
	public function __construct(CriteriaProperty $criteriaProperty, $labelLstr) {
		$this->criteriaProperty = $criteriaProperty;
		$this->labelLstr = Lstr::create($labelLstr);
	}
	
	public function getLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}
	
	private function readValue(Attributes $attributes): bool {
		return (new LenientAttributeReader($attributes))->getBool(self::ATTR_VALUE_KEY, self::ATTR_VALUE_DEFAULT);
	}
	
	public function createMagDispatchable(Attributes $attributes): MagDispatchable {
		$magCollection = new MagCollection();
		$magCollection->addMag(new BoolMag(self::ATTR_VALUE_KEY, $this->labelLstr, $this->readValue($attributes)));
		return new MagForm($magCollection);
	}
	
	public function buildAttributes(MagDispatchable $magDispatchable): Attributes {
		return new Attributes($magDispatchable->getMagCollection()->readValues());
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\critmod\filter\impl\field\FilterField::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint {
		return new SimpleComparatorConstraint($this->criteriaProperty, CriteriaComparator::OPERATOR_EQUAL,
				$this->readValue($attributes));
	}
	
	public function createMappableConstraint(Attributes $attributes): MappableConstraint {
		return new BoolMappableConstraint($this->labelLstr, $this->readValue($attributes)); 
	}
}



class BoolMappableConstraint implements MappableConstraint {
	private $labelLstr;
	private $acceptedValue;

	public function __construct(Lstr $labelLstr, bool $acceptedValue) {
		$this->labelLstr = $labelLstr;
		$this->acceptedValue = $acceptedValue;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\MappableConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		return $this->acceptedValue === $value;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\MappableConstraint::check($mappable)
	 */
	public function check(Mappable $mappable): bool {
		return $this->acceptsValue($mappable->getValue()); 
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\MappableConstraint::validate($mappable, $fieldErrorInfo)
	 */
	public function validate(Mappable $mappable, FieldErrorInfo $fieldErrorInfo) {
		if ($this->check($mappable)) return;

		$fieldErrorInfo->addError(new MessageCode('ei_impl_bool_field_must_be_selected_err', 
				array('field' => $this->labelLstr)));
	}
}
