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
namespace rocket\ei\manage\critmod\filter\impl\field;

use rocket\core\model\Rocket;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\config\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\ei\manage\critmod\filter\impl\model\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\ei\manage\critmod\filter\EiEntryFilterField;
use rocket\ei\manage\mapping\EiFieldConstraint;
use rocket\ei\manage\mapping\EiField;
use rocket\ei\manage\mapping\FieldErrorInfo;
use n2n\l10n\MessageCode;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\filter\impl\model\PropertyValueComparatorConstraint;

class BoolFilterField implements EiEntryFilterField {
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
		$magCollection->addMag(self::ATTR_VALUE_KEY, new BoolMag($this->labelLstr, $this->readValue($attributes)));
		return new MagForm($magCollection);
	}
	
	public function buildAttributes(MagDispatchable $magDispatchable): Attributes {
		return new Attributes($magDispatchable->getMagCollection()->readValues());
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\critmod\filter\impl\field\FilterField::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint {
		return new PropertyValueComparatorConstraint($this->criteriaProperty, CriteriaComparator::OPERATOR_EQUAL,
				CrIt::c($this->readValue($attributes)));
	}
	
	public function createEiFieldConstraint(Attributes $attributes): EiFieldConstraint {
		return new BoolEiFieldConstraint($this->labelLstr, $this->readValue($attributes)); 
	}
}



class BoolEiFieldConstraint implements EiFieldConstraint {
	private $labelLstr;
	private $acceptedValue;

	public function __construct(Lstr $labelLstr, bool $acceptedValue) {
		$this->labelLstr = $labelLstr;
		$this->acceptedValue = $acceptedValue;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiFieldConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		return $this->acceptedValue === $value;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiFieldConstraint::check($eiField)
	 */
	public function check(EiField $eiField): bool {
		return $this->acceptsValue($eiField->getValue()); 
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiFieldConstraint::validate($eiField, $fieldErrorInfo)
	 */
	public function validate(EiField $eiField, FieldErrorInfo $fieldErrorInfo) {
		if ($this->check($eiField)) return;

		$fieldErrorInfo->addError(new MessageCode('ei_impl_bool_field_must_be_selected_err', 
				array('field' => $this->labelLstr)));
	}
}
