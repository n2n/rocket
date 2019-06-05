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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\impl\ei\component\prop\adapter\entry\CrwvEiField;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\entry\EiFieldValidationResult;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\adapter\entry\Readable;
use rocket\impl\ei\component\prop\adapter\entry\Writable;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\entry\Copyable;

class ToManyEiField extends CrwvEiField {
	private $copyable = null;
	
	public function __construct(Eiu $eiu, Readable $readable = null, Writable $writable = null, 
			Copyable $copyable = null) {
		parent::__construct(null, $eiu, $readable, $writable);
		
		$this->copyable = $copyable;
	}	
	
	protected function checkValue($value) {
		ArgUtils::valArray($value, RelationEntry::class);
	}
	
	protected function readValue() {
		$targetRelationEntries = array();
		foreach (parent::readValue() as $targetEiObject) {
			$targetRelationEntries[] = RelationEntry::from($targetEiObject);
		}
		return $targetRelationEntries;	
	}
	
	protected function writeValue($value) {
		$targetEiObjects = array();
		foreach ($value as $targetRelationEntry) {
			if ($targetRelationEntry->hasEiEntry()) {
				$targetRelationEntry->getEiEntry()->write();
			}
			
			$targetEiObjects[] = $targetRelationEntry->getEiObject();
		}
		
		parent::writeValue($targetEiObjects);
	}
	

	public function validateValue($value, EiFieldValidationResult $validationResult) {
		if ($value === null) return;
		
		foreach ($value as $targetRelationEntry) {
			IllegalStateException::assertTrue($targetRelationEntry instanceof RelationEntry);
			if ($targetRelationEntry->hasEiEntry()) {
				$targetRelationEntry->getEiEntry()->validate();
				$validationResult->addSubEiEntryValidationResult($targetRelationEntry->getEiEntry()->getValidationResult());
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::copyEiField($eiObject)
	 */
	public function copyEiField(Eiu $copyEiu) {
		if ($this->copyable === null) return null;
		
		$copy = new ToManyEiField($copyEiu, $this->readable, $this->writable, 
				$this->copyable);
		$copy->setValue($this->copyable->copy($this->eiu, $this->getValue(), $copyEiu));
		return $copy;
	}
	
	public function copyValue(Eiu $copyEiu) {
		if ($this->copyable === null) return null;
		
		return $this->copyable->copy($this->eiFieldMap, $this->getValue(), $copyEiu);
	}
}
