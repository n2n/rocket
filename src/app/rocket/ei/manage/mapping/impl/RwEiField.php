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
namespace rocket\ei\manage\mapping\impl;

use rocket\ei\manage\EiObject;
use rocket\ei\manage\mapping\MappingOperationFailedException;
use rocket\ei\manage\mapping\EiFieldConstraint;
use rocket\ei\manage\mapping\EiField;
use rocket\ei\manage\mapping\FieldErrorInfo;

abstract class RwEiField extends EiFieldAdapter {
	protected $readable;
	protected $writable;
	protected $validatable;

	public function __construct(EiObject $eiObject, Readable $readable = null, Writable $writable = null, 
			Validatable $validatable = null) {
		parent::__construct($eiObject);
		$this->readable = $readable;
		$this->writable = $writable;
		$this->validatable = $validatable;
		
		if ($validatable !== null) {
			$this->getEiFieldConstraintSet()->add(new ValidatableEiFieldConstraint($eiObject, $validatable));
		}
	}

	public function isReadable(): bool {
		return $this->readable !== null;
	}

	protected function readValue() {
		if (null !== $this->readable) {
			$value = $this->readable->read($this->eiObject);
			// @todo convert exception to better exception
			return $value;
		}

		throw new MappingOperationFailedException('EiField is not readable.');
	}

	public function isWritable(): bool {
		return $this->writable !== null;
	}
	
	protected function writeValue($value) {
		if (null !== $this->writable) {
			$this->writable->write($this->eiObject, $value);
			return;
		}

		throw new MappingOperationFailedException('EiField is not writable.');
	}
}

class ValidatableEiFieldConstraint implements EiFieldConstraint {
	private $eiObject;
	private $validatable;
	
	public function __construct(EiObject $eiObject, Validatable $validatable) {
		$this->eiObject = $eiObject;
		$this->validatable = $validatable;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiFieldConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		$this->validatable->testEiFieldValue($this->eiObject, $value);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiFieldConstraint::check($eiField)
	 */
	public function check(EiField $eiField): bool {
		return $this->validatable->testEiFieldValue($this->eiObject, $eiField->getValue());	
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\EiFieldConstraint::validate($eiField, $fieldErrorInfo)
	 */
	public function validate(EiField $eiField, FieldErrorInfo $fieldErrorInfo) {
		return $this->validatable->validateEiFieldValue($this->eiObject, $eiField->getValue(), $fieldErrorInfo);
	}	
}
