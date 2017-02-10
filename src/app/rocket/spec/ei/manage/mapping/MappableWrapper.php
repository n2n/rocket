<?php

namespace rocket\spec\ei\manage\mapping;

class MappableWrapper {
	private $mappable;
	private $ignored = false;
	
	public function __construct(Mappable $mappable) {
		$this->mappable = $mappable;
	}
	
	/**
	 * @param bool $ignored
	 */
	public function setIgnored(bool $ignored) {
		$this->ignored = $ignored;
	}
	
	/**
	 * @return bool
	 */
	public function isIgnored() {
		return $this->ignored;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\mapping\Mappable
	 */
	public function getMappable() {
		return $this->mappable;
	}
}