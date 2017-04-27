<?php
namespace rocket\spec\ei\manage\mapping\impl;

use rocket\spec\ei\manage\mapping\MappableWrapper;

class MappableWrapperWrapper implements MappableWrapper {
	private $mappableWrappers = array();
	
	public function __construct(array $mappableWrappers) {
		$this->mappableWrappers = $mappableWrappers;
	}
	
	public function isIgnored(): bool {
		foreach ($this->mappableWrappers as $mappableWrapper) {
			if (!$mappableWrapper->isIgnored()) return false;
		}
		
		return true;
	}
	
	public function setIgnored(bool $ignored) {
		foreach ($this->mappableWrappers as $mappableWrapper) {
			$mappableWrapper->setIgnored($ignored);
		}
	}
}