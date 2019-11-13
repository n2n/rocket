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

use rocket\ei\util\Eiu;
use n2n\util\type\TypeConstraint;
use rocket\ei\manage\entry\EiFieldOperationFailedException;
use n2n\util\type\TypeUtils;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use rocket\ei\manage\entry\EiFieldValidationResult;

class RwvcEiField extends EiFieldAdapter {
	private $eiu;
	
	private $nullReadAllowed = true;
	private $reader;
	private $writer;
	private $validator;
	private $copier;
	
	public function __construct(Eiu $eiu, ?TypeConstraint $typeConstraint, StatelessEiFieldReader $reader, 
			StatelessEiFieldWriter $writer, StatelessEiFieldValidator $validator = null, 
			StatelessEiFieldCopier $copier = null) {
		parent::__construct($typeConstraint);
		
		$this->eiu = $eiu;
		$this->reader = $reader;
		$this->writer = $writer;
		$this->validator = $validator;
		$this->copier = $copier;
	}
	
	protected function checkValue($value) {
		if ($this->typeConstraint === null) return;
		
		$this->typeConstraint->validate($value);
	}
	
	
	public function isWritable(): bool {
		return $this->writer !== null;
	}
	
	protected function writeValue($value) {
		if (null !== $this->writer) {
			$this->writer->writeEiFieldValue($this->eiu, $value);
			return;
		}
		
		throw new EiFieldOperationFailedException('EiField is not writable.');
	}
	
// 	public function isDraft() {
// 		return $this->eiObject->isDraft();
// 	}

	protected function readValue() {
		$value = $this->reader->readEiFieldValue($this->eiu);
		
		if ($this->nullReadAllowed && $value === null) {
			return $value;
		}
		
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
	
// 	public function copyEiField(Eiu $copyEiu): ?EiField {
// 		if ($this->copyable === null) return null;
		
// 		$eiField = new EiFieldProxy($copyEiu, $this->typeConstraint, $this->readable, $this->writable,
// 				$this->validatable, $this->copyable);
// 		$eiField->setNullReadAllowed($this->isNullReadAllowed());
// 		$eiField->setValue($this->copyable->copy($this->eiu, $this->getValue(), $copyEiu));
// 		return $eiField;
// 	}
	
	public function isCopyable(): bool {
		return $this->copier !== null;
	}
	
	public function copyValue(Eiu $copyEiu) {
		if ($this->copier === null) return null;
		
		return $this->copier->copyEiFieldValue($this->eiu, $this->getValue(), $copyEiu);
	}
	
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		if ($this->validator !== null) {
			$this->validator->validateEiFieldValue($this->eiu, $value, $validationResult);
		}
	}
	
	protected function isValueValid($value) {
		return $this->validator === null || $this->validator->acceptsEiFieldValue($this->eiu, $value);
	}
}
