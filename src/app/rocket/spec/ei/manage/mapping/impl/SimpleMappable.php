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

use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\manage\EiObject;
use n2n\reflection\property\ValueIncompatibleWithConstraintsException;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\util\model\Eiu;

class SimpleMappable extends RwMappable {
	private $typeConstraint;
	private $nullReadAllowed = true;
	
	public function __construct(EiObject $eiObject, TypeConstraint $typeConstraint, 
			Readable $readable = null, Writable $writable = null, Validatable $validatable = null) {
		parent::__construct($eiObject, $readable, $writable, $validatable);
		$this->typeConstraint = $typeConstraint;
	}
	
	public function isNullReadAllowed(): bool {
		return $this->nullReadAllowed;
	}
	
	public function setNullReadAllowed(bool $nullReadAllowed) {
		$this->nullReadAllowed = $nullReadAllowed;
	}
	
// 	public function isDraft() {
// 		return $this->eiObject->isDraft();
// 	}

	protected function readValue() {
		$value = parent::readValue();
		if ($this->nullReadAllowed && $value === null) {
			return $value;
		}
		
		try {
			$this->validateValue($value);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(ReflectionUtils::prettyMethName(get_class($this->readable), 'read')
					. ' returns invalid argument.', 0, $e->getPrevious());
		}
		
		return $value;
	}
	
	protected function validateValue($value) {
		if ($this->typeConstraint === null) return;
		
		try {
			$this->typeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new \InvalidArgumentException('Mappable can not adopt passed value.', 0, $e);
		}
	}
	
	public function copyMappable(Eiu $eiu) {
		$mappable = new SimpleMappable($eiu->entry()->getEiSelection(), $this->typeConstraint, $this->readable, $this->writable, 
				$this->validatable);
		$mappable->setNullReadAllowed($this->isNullReadAllowed());
// 		if ($this->isValueLoaded()) {
			$mappable->setValue($this->getValue());
// 		}
		return $mappable;
	}
}
