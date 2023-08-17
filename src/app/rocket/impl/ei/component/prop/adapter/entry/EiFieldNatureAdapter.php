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

use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\manage\entry\EiFieldValidationResult;
use rocket\op\ei\manage\entry\EiFieldMap;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;

abstract class EiFieldNatureAdapter implements EiFieldNature {
// 	protected $typeConstraint;
	protected bool $valueLoaded = false;
	protected mixed $value;
	private bool $changed = false;

	public function __construct(/*TypeConstraint $typeConstraint = null*/) {
// 		$this->typeConstraint = $typeConstraint;
// 		$this->eiFieldConstraintSet = new HashSet(EiFieldConstraint::class);
	}
	
// 	function getTypeConstraint(): ?TypeConstraint {
// 		return $this->typeConstraint;
// 	}
	
	private function assetConstraints($value) {
		try {
			$this->checkValue($value);
		} catch (\InvalidArgumentException|ValueIncompatibleWithConstraintsException $e) {
			throw new ValueIncompatibleWithConstraintsException('EiField can not adopt passed value.', 0, $e);
		}

// 		throw new ValueIncompatibleWithConstraintsException('EiField can not adopt passed value.');
	}
	
	/**
	 * @param mixed $value
	 * @throws ValueIncompatibleWithConstraintsException
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	protected abstract function checkValue($value);

	/**
	 * @return bool
	 */
	public final function isValueLoaded(): bool {
		return $this->valueLoaded;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\entry\EiFieldNature::getValue()
	 */
	public final function getValue() {
		if ($this->valueLoaded) {
			return $this->value;
		}
		
		$this->read();

		return $this->value;
	}
	
	public final function read() {
		$this->value = $this->readValue();
		$this->valueLoaded = true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\entry\EiFieldNature::setValue()
	 */
	public final function setValue($value) {
		$this->assetConstraints($value);

		$this->value = $value;
		$this->valueLoaded = true;
		$this->changed = true;
	}
	
	final function hasChanges(): bool {
		return $this->changed || ($this->hasForkedEiFieldMap() && $this->getForkedEiFieldMap()->hasChanges());
	}


// 	/**
// 	 * @param mixed $value
// 	 * @throws ValueIncompatibleWithConstraintsException
// 	 */
// 	protected abstract function checkValue($value);

	/**
	 * 
	 */
	protected abstract function readValue();

// 	/**
// 	 * @return Set
// 	 */
// 	public function getEiFieldConstraintSet() {
// 		if ($this->eiFieldConstraintSet === null) {
// 			$this->eiFieldConstraintSet = new HashSet(EiFieldConstraint::class);
// 		}
// 		return $this->eiFieldConstraintSet;
// 	}

	public function acceptsValue($value): bool {
		$this->assetConstraints($value);
		
		return $this->isValueValid($value);
	}

	public function isValid(): bool {
		return $this->isValueValid($this->getValue());
	}
	
	/**
	 * @param mixed $value
	 */
	protected abstract function isValueValid($value);

	public final function validate(EiFieldValidationResult $validationResult) {
		$this->validateValue($this->getValue(), $validationResult);
	}

	/**
	 * @param mixed $value
	 * @param EiFieldValidationResult $validationResult
	 */
	protected abstract function validateValue($value, EiFieldValidationResult $validationResult);
	
	public final function write() {
		IllegalStateException::assertTrue($this->isWritable());
		if (!$this->valueLoaded || !$this->hasChanges()) {
			return;
		}
		
		$this->writeValue($this->value);
		$this->changed = false;
	}

	protected abstract function writeValue($value);
	
	public function hasForkedEiFieldMap(): bool {
		return false;
	}
	
	public function getForkedEiFieldMap(): EiFieldMap {
		throw new IllegalStateException('No ForkedEiFieldMap available.');
	}
}
