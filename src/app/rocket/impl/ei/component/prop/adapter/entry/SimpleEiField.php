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

use n2n\util\type\TypeConstraint;
use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiField;
use n2n\util\type\TypeUtils;
use n2n\util\type\ValueIncompatibleWithConstraintsException;

class SimpleEiField extends CrwvEiField {
	private $copyable;
	private $nullReadAllowed = true;
	
	public function __construct(Eiu $eiu, TypeConstraint $typeConstraint, 
			Readable $readable = null, Writable $writable = null, Validatable $validatable = null, Copyable $copyable = null) {
		parent::__construct($typeConstraint, $eiu, $readable, $writable, $validatable);
		$this->copyable = $copyable;
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
		
		$e = null;
		try {
			$this->checkValue($value);
			return $value;
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(TypeUtils::prettyMethName(get_class($this->readable), 'read')
					. ' returns invalid argument.', 0, $e->getPrevious());
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new \InvalidArgumentException(TypeUtils::prettyMethName(get_class($this->readable), 'read')
					. ' returns invalid argument.', 0, $e->getPrevious());
		}
		
		return $value;
	}
	
	public function copyEiField(Eiu $copyEiu): ?EiField {
		if ($this->copyable === null) return null;
		
		$eiField = new SimpleEiField($copyEiu, $this->typeConstraint, $this->readable, $this->writable,
				$this->validatable, $this->copyable);
		$eiField->setNullReadAllowed($this->isNullReadAllowed());
		$eiField->setValue($this->copyable->copy($this->eiu, $this->getValue(), $copyEiu));
		return $eiField;
	}
	
	public function copyValue(Eiu $copyEiu) {
		if ($this->copyable === null) return null;
		
		return $this->copyable->copy($this->eiu, $this->getValue(), $copyEiu);
	}
}
