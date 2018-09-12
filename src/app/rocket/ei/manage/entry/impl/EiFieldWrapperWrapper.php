<?php
namespace rocket\ei\manage\mapping\impl;

use rocket\ei\manage\mapping\EiFieldWrapper;

class EiFieldWrapperWrapper implements EiFieldWrapper {
	private $eiFieldWrappers = array();
	
	public function __construct(array $eiFieldWrappers) {
		$this->eiFieldWrappers = $eiFieldWrappers;
	}
	
	public function isIgnored(): bool {
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			if (!$eiFieldWrapper->isIgnored()) return false;
		}
		
		return true;
	}
	
	public function setIgnored(bool $ignored) {
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			$eiFieldWrapper->setIgnored($ignored);
		}
	}
}