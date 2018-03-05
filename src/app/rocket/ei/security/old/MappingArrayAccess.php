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
namespace rocket\ei\manage\security;

use rocket\ei\manage\mapping\EiEntry;
use n2n\util\ex\UnsupportedOperationException;

class MappingArrayAccess implements \ArrayAccess {
	private $mapping;
	private $testOrgValues;
	
	public function __construct(EiEntry $mapping, $testOrgValues) {
		$this->mapping = $mapping;
		$this->testOrgValues = $testOrgValues;
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return $this->mapping->getMappingDefinition()->containsId($offset);
	}

	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		if ($this->testOrgValues) {
			return $this->mapping->getOrgValue($offset);
		}
		return $this->mapping->getValue($offset);
	}

	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		if ($this->testOrgValues) {
			throw new UnsupportedOperationException('Cannot set org value.');	
		}
		$this->mapping->setValue($offset, $value);
	}

	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		throw new UnsupportedOperationException();
	}
}
