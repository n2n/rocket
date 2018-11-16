<?php
namespace rocket\ei\component\prop\field;

use rocket\ei\manage\gui\EiFieldAbstraction;

class EiFieldWrapperWrapper implements EiFieldAbstraction {
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