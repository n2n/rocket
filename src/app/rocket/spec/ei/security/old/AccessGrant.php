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
// namespace rocket\spec\ei\manage\security;

// use n2n\util\config\Attributes;
// use rocket\spec\ei\manage\critmod\SelectorValidationResult;

// class AccessGrant {
// 	private $accessAttributes;
// 	private $restrictionSelector;
	
// 	public function __construct(Attributes $accessAttributes = null, Selector $restrictionSelector = null) {
// 		$this->accessAttributes = $accessAttributes;
// 		$this->restrictionSelector = $restrictionSelector;
// 	}
	
// 	public function isRestricted() {
// 		return $this->accessAttributes !== null;
// 	}
	
// 	public function getAttributesById($id) {
// 		return new Attributes($this->accessAttributes->get($id));
// 	}

// 	public function acceptsValues(\ArrayAccess $values) {
// 		if ($this->restrictionSelector === null) return true;
// 		return $this->restrictionSelector->acceptsValues($values);
// 	}
	
// 	public function validateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
// 		if ($this->restrictionSelector === null) return true;
// 		return $this->restrictionSelector->validateValues($values, $validationResult);
// 	}
	
// 	public function acceptsValue($id, $value) {
// 		if ($this->restrictionSelector === null) return true;
// 		return $this->restrictionSelector->acceptsValue($id, $value);
// 	}
	
// // 	public function matchValues(array $values) {
		
// // 	}
// }
