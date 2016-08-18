<?php

namespace rocket\script\entity\security;

use rocket\script\entity\filter\data\FilterData;
use rocket\script\entity\manage\security\PrivilegeBuilder;
use rocket\script\entity\filter\SelectorValidationResult;

class PrivilegesGrantItem {
	private $privileges;
	private $restrictionFilterData;
	private $selector;
	private $comparatorConstraint;
	private $constraint;
	
	public function __construct(array $privileges, FilterData $restrictionFitlerData = null, CommonEntityScriptConstraint $constraint) {
		$this->privileges = $privileges;
		$this->restrictionFilterData = $restrictionFitlerData;
		$this->selector = null;
		$this->constraint = $constraint;
	}
	
	public function isRestricted() {
		return $this->restrictionFilterData !== null;
	}
	
	public function isPrivilegeAccessable($privilege) {
		if (PrivilegeBuilder::testPrivileges($privilege, $this->privileges)) {
			return true;
		}
		
		return false;
	}
	
	public function getPrivileges() {
		return $this->privileges;
	}
	
	private function getSelector() {
		if ($this->selector === null) {
			$this->selector = $this->constraint->getOrCreateSelectorModel()
					->createSelector($this->restrictionFilterData);
		}
		
		return $this->selector;
	}
	
	public function acceptsValues(\ArrayAccess $values) {
		if (!$this->isRestricted()) return true;
		return $this->getSelector()->acceptsValues($values);
	}
	
	public function acceptValue($id, $value) {
		if (!$this->isRestricted()) return true;
		return $this->getSelector()->acceptValue($id, $value);
	}
	
	public function validateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
		if (!$this->isRestricted()) return;
		return $this->getSelector()->validateValues($values, $validationResult);
	}
	
	public function getComparatorConstraint() {
		if (!$this->isRestricted()) return null;
		if ($this->comparatorConstraint === null) {	
			$this->comparatorConstraint = $this->constraint->getOrCreateFilterModel()
					->createComparatorConstraint($this->restrictionFilterData);
		}
		
		return $this->comparatorConstraint;
	}
	
	
}