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
namespace rocket\impl\ei\component\prop\adapter\entry;

use rocket\ei\manage\entry\EiFieldOperationFailedException;
use rocket\ei\manage\entry\EiFieldConstraint;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\util\Eiu;
use n2n\util\type\TypeConstraint;
use n2n\util\type\ArgUtils;

abstract class CrwvEiField extends EiFieldAdapter {
	protected $typeConstraint;
	protected $eiu;
	protected $readable;
	protected $writable;
	protected $validatable;

	public function __construct(TypeConstraint $typeConstriant = null, Eiu $eiu = null, Readable $readable = null, 
			Writable $writable = null, Validatable $validatable = null) {
		$this->typeConstraint = $typeConstriant;
		ArgUtils::assertTrue($eiu === null || $readable !== null || $writable !== null || $validatable !== null);
		$this->eiu = $eiu;
		$this->readable = $readable;
		$this->writable = $writable;
		$this->validatable = $validatable;
	}
	
	
	protected function checkValue($value) {
		if ($this->typeConstraint === null) return;
		
		$this->typeConstraint->validate($value);
	}
	
	public function isReadable(): bool {
		return $this->readable !== null;
	}

	protected function readValue() {
		if (null !== $this->readable) {
			$value = $this->readable->read($this->eiu);
			// @todo convert exception to better exception
			return $value;
		}

		throw new EiFieldOperationFailedException('EiField is not readable.');
	}

	public function isWritable(): bool {
		return $this->writable !== null;
	}
	
	protected function writeValue($value) {
		if (null !== $this->writable) {
			$this->writable->write($this->eiu, $value);
			return;
		}

		throw new EiFieldOperationFailedException('EiField is not writable.');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::testValue()
	 */
	protected function isValueValid($value) {
		$this->checkValue($value);
		
		if ($this->validatable === null) {
			return true;
		}
		
		return $this->validatable->testEiFieldValue($this->eiu, $value);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::validate()
	 */
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		$this->checkValue($value);
		
		if ($this->validatable === null) {
			return;
		}
		
		return $this->validatable->validateEiFieldValue($this->eiu, $value, $validationResult);
	}
}

class ValidatableEiFieldConstraint implements EiFieldConstraint {
	private $eiu;
	private $validatable;
	
	public function __construct(Eiu $eiu, Validatable $validatable) {
		$this->eiu = $eiu;
		$this->validatable = $validatable;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		$this->validatable->testEiFieldValue($this->eiu, $value);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::check($eiField)
	 */
	public function check(EiField $eiField): bool {
		return $this->validatable->testEiFieldValue($this->eiu, $eiField->getValue());	
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::validate($eiField, $validationResult)
	 */
	public function validate(EiField $eiField, EiFieldValidationResult $validationResult) {
		return $this->validatable->validateEiFieldValue($this->eiu, $eiField->getValue(), $validationResult);
	}	
}
