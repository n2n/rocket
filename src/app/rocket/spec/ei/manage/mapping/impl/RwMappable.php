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
namespace rocket\spec\ei\manage\mapping\impl;

use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\MappingOperationFailedException;
use rocket\spec\ei\manage\mapping\MappableConstraint;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;

abstract class RwMappable extends MappableAdapter {
	protected $readable;
	protected $writable;
	protected $validatable;

	public function __construct(EiObject $eiObject, Readable $readable = null, Writable $writable = null, 
			Validatable $validatable = null, Copyable $copyable = null) {
		parent::__construct($eiObject);
		$this->readable = $readable;
		$this->writable = $writable;
		$this->validatable = $validatable;
		$this->copyable = $copyable;
		
		if ($validatable !== null) {
			$this->getMappableConstraintSet()->add(new ValidatableMappableConstraint($eiObject, $validatable));
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

		throw new MappingOperationFailedException('Mappable is not readable.');
	}

	public function isWritable(): bool {
		return $this->writable !== null;
	}
	
	protected function writeValue($value) {
		if (null !== $this->writable) {
			$this->writable->write($this->eiObject, $value);
			return;
		}

		throw new MappingOperationFailedException('Mappable is not writable.');
	}
}

class ValidatableMappableConstraint implements MappableConstraint {
	private $eiObject;
	private $validatable;
	
	public function __construct(EiObject $eiObject, Validatable $validatable) {
		$this->eiObject = $eiObject;
		$this->validatable = $validatable;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\MappableConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		$this->validatable->testMappableValue($this->eiObject, $value);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\MappableConstraint::check($mappable)
	 */
	public function check(Mappable $mappable): bool {
		return $this->validatable->testMappableValue($this->eiObject, $mappable->getValue());	
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\MappableConstraint::validate($mappable, $fieldErrorInfo)
	 */
	public function validate(Mappable $mappable, FieldErrorInfo $fieldErrorInfo) {
		return $this->validatable->validateMappableValue($this->eiObject, $mappable->getValue(), $fieldErrorInfo);
	}	
}
