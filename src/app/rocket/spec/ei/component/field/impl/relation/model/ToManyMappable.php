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

use rocket\spec\ei\manage\mapping\impl\RwMappable;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\util\ex\IllegalStateException;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\manage\EiObject;

class ToManyMappable extends RwMappable  {
	
	protected function validateValue($value) {
		ArgUtils::valArray($value, RelationEntry::class);
	}
	
	protected function readValue() {
		$targetRelationEntries = array();
		foreach (parent::readValue() as $targetEiSelection) {
			$targetRelationEntries[] = RelationEntry::from($targetEiSelection);
		}
		return $targetRelationEntries;	
	}
	
	protected function writeValue($value) {
		$targetEiSelections = array();
		foreach ($value as $targetRelationEntry) {
			if ($targetRelationEntry->hasEiMapping()) {
				$targetRelationEntry->getEiMapping()->write();
			}
			
			$targetEiSelections[] = $targetRelationEntry->getEiSelection();
		}
		
		parent::writeValue($targetEiSelections);
	}
	

	public function validate(FieldErrorInfo $fieldErrorInfo) {
		$value = $this->getValue();
		if ($value === null) return;
		
		foreach ($value as $targetRelationEntry) {
			IllegalStateException::assertTrue($targetRelationEntry instanceof RelationEntry);
			if ($targetRelationEntry->hasEiMapping()) {
				$targetRelationEntry->getEiMapping()->validate();
				$fieldErrorInfo->addSubMappingErrorInfo($targetRelationEntry->getEiMapping()->getMappingErrorInfo());
			}
		}
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\Mappable::copyMappable($eiObject)
	 */
	public function copyMappable(EiObject $eiObject) {
		$copy = new ToManyMappable($eiObject, $this->readable, $this->writable);
		$copy->setValue($this->getValue());
		return $copy;
	}

}