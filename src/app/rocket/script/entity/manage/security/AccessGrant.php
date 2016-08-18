<?php

namespace rocket\script\entity\manage\security;

use n2n\util\Attributes;
use rocket\script\entity\filter\Selector;
use rocket\script\entity\filter\SelectorValidationResult;

class AccessGrant {
	private $accessAttributes;
	private $restrictionSelector;
	
	public function __construct(Attributes $accessAttributes = null, Selector $restrictionSelector = null) {
		$this->accessAttributes = $accessAttributes;
		$this->restrictionSelector = $restrictionSelector;
	}
	
	public function isRestricted() {
		return $this->accessAttributes !== null;
	}
	
	public function getAttributesById($id) {
		return new Attributes($this->accessAttributes->get($id));
	}

	public function acceptsValues(\ArrayAccess $values) {
		if ($this->restrictionSelector === null) return true;
		return $this->restrictionSelector->acceptsValues($values);
	}
	
	public function validateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
		if ($this->restrictionSelector === null) return true;
		return $this->restrictionSelector->validateValues($values, $validationResult);
		
	}
	
	public function acceptsValue($id, $value) {
		if ($this->restrictionSelector === null) return true;
		return $this->restrictionSelector->acceptsValue($id, $value);
	}
	
// 	public function matchValues(array $values) {
		
// 	}
}