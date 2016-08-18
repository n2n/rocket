<?php

namespace rocket\util;

use n2n\util\Set;
use n2n\reflection\ArgumentUtils;
use n2n\core\UnsupportedOperationException;
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
		ArgumentUtils::validateType($arg, $this->genericType);
		ArgumentUtils::assertTrue($arg instanceof Identifiable);
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
		ArgumentUtils::assertTrue($arg instanceof Identifiable);
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
		ArgumentUtils::assertTrue($arg instanceof Identifiable);
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