<?php
namespace rocket\ei\manage\entry\impl;

use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\entry\EiFieldWrapper;

class EiFieldWrapperImpl implements EiFieldWrapper {
	private $eiField;
	private $ignored = false;
	
	public function __construct(EiField $eiField) {
		$this->eiField = $eiField;
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
	public function isIgnored(): bool {
		return $this->ignored;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiField
	 */
	public function getEiField() {
		return $this->eiField;
	}
}