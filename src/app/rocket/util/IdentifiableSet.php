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
namespace rocket\util;

use n2n\util\Set;
use n2n\reflection\ArgUtils;
use n2n\util\ex\UnsupportedOperationException;
use n2n\core\NotYetImplementedException;

class IdentifiableSet implements Set, \ArrayAccess {
	private $values = array();
	private $genericType;
	
	public function __construct($genericType = null) {
		$this->genericType = $genericType;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::add()
	 */
	public function add($arg) {
		ArgUtils::valType($arg, $this->genericType);
		ArgUtils::assertTrue($arg instanceof Identifiable);
		$this->values[$arg->getId()] = $arg;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::addAll()
	 */
	public function addAll(array $args) {
		throw new NotYetImplementedException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::remove()
	 */
	public function remove($arg) {
		ArgUtils::assertTrue($arg instanceof Identifiable);
		$this->offsetUnset($id);
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::removeAll()
	 */
	public function removeAll(array $args) {
		throw new NotYetImplementedException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::clear()
	 */
	public function clear() {
		$this->values = array();
	}

	/* (non-PHPdoc)
	 * @see \n2n\util\Set::contains()
	 */
	public function contains($arg) {
		ArgUtils::assertTrue($arg instanceof Identifiable);
		return $this->offsetExists($arg->getId());
	}

	/* (non-PHPdoc)
	 * @see \n2n\util\Set::isEmpty()
	 */
	public function isEmpty() {
		return empty($this->values);
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::toArray()
	 */
	public function toArray() {
		return $this->values;
	}
	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->values);	
	}
	/* (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count() {
		return sizeof($this->values);	
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return isset($this->values[$offset]);
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		if (isset($this->values[$offset])) {
			return $this->values[$offset];
		}	
		return null;
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		throw new UnsupportedOperationException('use add()');	
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		unset($this->values[$offset]);
	}
	
	public function getIds() {
		return array_keys($this->values);
	}
}
