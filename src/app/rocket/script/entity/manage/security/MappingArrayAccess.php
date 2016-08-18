<?php

namespace rocket\script\entity\manage\security;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\core\UnsupportedOperationException;

class MappingArrayAccess implements \ArrayAccess {
	private $mapping;
	private $testOrgValues;
	
	public function __construct(ScriptSelectionMapping $mapping, $testOrgValues) {
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