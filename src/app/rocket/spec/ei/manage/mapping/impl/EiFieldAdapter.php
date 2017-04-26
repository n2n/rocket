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

use rocket\spec\ei\manage\mapping\EiField;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\util\col\HashSet;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\EiFieldConstraint;
use n2n\util\col\Set;

abstract class EiFieldAdapter implements EiField {
	protected $eiObject;
	protected $valueLoaded = false;
	protected $value;
	protected $orgValueLoaded = false;
	protected $orgValue;
	protected $eiFieldConstraintSet;

	public function __construct(EiObject $eiObject) {
		$this->eiObject = $eiObject;
		$this->eiFieldConstraintSet = new HashSet(EiFieldConstraint::class);
	}

	public function isValueLoaded(): bool {
		return $this->valueLoaded;
	}

	public function getValue() {
		if ($this->valueLoaded) {
			return $this->value;
		}

		return $this->getOrgValue();
	}

	public function isOrgValueLoaded() {
		return $this->orgValueLoaded;
	}
	
	public function getOrgValue() {
		if ($this->orgValueLoaded) {
			return $this->orgValue;
		}

		$this->orgValue = $this->readValue();
		$this->orgValueLoaded = true;
		return $this->orgValue;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\mapping\EiField::setValue()
	 */
	public function setValue($value) {
		$this->validateValue($value);

		$this->value = $value;
		$this->valueLoaded = true;
	}

	public function resetValue() {
		$this->value = null;
		$this->valueLoaded = false;
	}

	/**
	 * @param unknown $value
	 * @throws \InvalidArgumentException
	 */
	protected abstract function validateValue($value);

	protected abstract function readValue();

	public function getEiFieldConstraintSet(): Set {
		return $this->eiFieldConstraintSet;
	}

	public function acceptsValue($value): bool {
		foreach ($this->eiFieldConstraintSet as $eiFieldConstraint) {
			if (!$eiFieldConstraint->acceptsValue()) return false;
		}

		return true;
	}

	public function check(): bool {
		foreach ($this->eiFieldConstraintSet as $eiFieldConstraint) {
			if (!$eiFieldConstraint->check()) return false;
		}

		return true;
	}

	public function validate(FieldErrorInfo $fieldErrorInfo) {
		foreach ($this->eiFieldConstraintSet as $eiFieldConstraint) {
			$eiFieldConstraint->validate($this, $fieldErrorInfo);
		}
	}

	public function write() {
		if (!$this->valueLoaded) return;
		
		$this->writeValue($this->value);
	}

	protected abstract function writeValue($value);
}