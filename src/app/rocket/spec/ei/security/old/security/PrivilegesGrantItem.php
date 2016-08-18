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
namespace rocket\spec\ei\security;

use rocket\spec\ei\manage\critmod\filter\data\FilterData;
use rocket\spec\ei\manage\security\PrivilegeBuilder;
use rocket\spec\ei\manage\critmod\SelectorValidationResult;

class PrivilegesGrantItem {
	private $privileges;
	private $restrictionFilterData;
	private $selector;
	private $comparatorConstraint;
	private $constraint;
	
	public function __construct(array $privileges, FilterData $restrictionFitlerData = null, CommonConstraint $constraint) {
		$this->privileges = $privileges;
		$this->restrictionFilterData = $restrictionFitlerData;
		$this->selector = null;
		$this->constraint = $constraint;
	}
	
	public function isRestricted() {
		return $this->restrictionFilterData !== null;
	}
	
	public function isPrivilegeaccessible($privilege) {
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
