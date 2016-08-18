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
use rocket\spec\ei\manage\mapping\impl\RwMappable;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;

class ToOneMappable extends RwMappable {
	
	protected function validateValue($value) {
		ArgUtils::valType($value, RelationEntry::class, true);
	}
	
	protected function readValue() {
		if (null !== ($targetEiSelection = parent::readValue())) {
			return RelationEntry::from($targetEiSelection);
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
			
		parent::writeValue($targetRelationEntry->getEiSelection());
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
	
	public function copyMappable(EiObject $eiObject) {
		throw new NotYetImplementedException();
	}
}
