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
namespace rocket\spec\ei\component\field\impl\relation\model;

use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\impl\RwEiField;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\impl\Readable;
use rocket\spec\ei\manage\mapping\impl\Writable;
use rocket\spec\ei\manage\mapping\impl\Copyable;

class ToOneEiField extends RwEiField {
	private $copyable;
	
	public function __construct(EiObject $eiObject, Readable $readable = null, Writable $writable = null,
			Copyable $copyable = null) {
		parent::__construct($eiObject, $readable, $writable);

		$this->copyable = $copyable;
	}
	
	
	protected function validateValue($value) {
		ArgUtils::valType($value, RelationEntry::class, true);
	}
	
	protected function readValue() {
		if (null !== ($targetEiObject = parent::readValue())) {
			return RelationEntry::from($targetEiObject);
		}
		
		return null;	
	}
	
	protected function writeValue($targetRelationEntry) {
		if ($targetRelationEntry === null) {
			parent::writeValue($targetRelationEntry);
			return;
		}
		
		if ($targetRelationEntry->hasEiMapping()) {
			$targetRelationEntry->getEiMapping()->write();
		}
			
		parent::writeValue($targetRelationEntry->getEiObject());
	}
	
	public function validate(FieldErrorInfo $fieldErrorInfo) {
		if (null !== ($value = $this->getValue())) {
			IllegalStateException::assertTrue($value instanceof RelationEntry);
			if ($value->hasEiMapping()) {
				$value->getEiMapping()->validate();
				$fieldErrorInfo->addSubMappingErrorInfo($value->getEiMapping()->getMappingErrorInfo());
			}
		}
	}
	
	public function copyEiField(Eiu $copyEiu) {
		if ($this->copyable === null) return null;
		
		$copy = new ToOneEiField($copyEiu->entry()->getEiObject(), $this->readable, $this->writable, $this->copyable);
		$copy->setValue($this->copyable->copy($this->eiObject, $this->getValue(), $copyEiu));
		return $copy;
	}
}