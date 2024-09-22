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
namespace rocket\op\ei\manage\entry;

use rocket\op\ei\manage\gui\EiFieldAbstraction;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\security\InaccessibleEiFieldException;
use n2n\util\type\ValueIncompatibleWithConstraintsException;

class EiField implements EiFieldAbstraction {
	private $eiFieldMap;
	private $eiPropPath;
	private $ignored = false;
	
	private $orgValueLoaded = false;
	private $orgValue;
	
	function __construct(EiFieldMap $eiFieldMap, EiPropPath $eiPropPath, private readonly EiFieldNature $eiFieldNature) {
		$this->eiFieldMap = $eiFieldMap;
		$this->eiPropPath = $eiPropPath;
	}

	function getEiFieldNature(): EiFieldNature {
		return $this->eiFieldNature;
	}
	
	/**
	 * @param bool $ignored
	 */
	function setIgnored(bool $ignored) {
		$this->ignored = $ignored;
	}
	
	/**
	 * @return bool
	 */
	function isIgnored(): bool {
		return $this->ignored;
	}
	
	/**
	 * @return \rocket\op\ei\manage\entry\EiFieldMap
	 */
	function getEiFieldMap() {
		return $this->eiFieldMap;
	}
	
	/**
	 * @return boolean
	 */
	final function isOrgValueLoaded() {
		return $this->orgValueLoaded;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\entry\EiFieldNature::getOrgValue()
	 */
	final function getOrgValue() {
		$this->ensureOrgLoaded();
		return $this->orgValue;
	}
	
	final function resetValue() {
		if (!$this->orgValueLoaded) {
			return;
		}
		
		$this->eiFieldNature->setValue($this->orgValue);
	}
	
	/**
	 * @return boolean
	 */
	final function hasChanges() {
		return $this->eiFieldNature->hasChanges();
	}
	
	final function read() {
		$this->eiFieldNature->read();
	}
	
	private function ensureOrgLoaded() {
		if ($this->orgValueLoaded) {
			return;
		}
		
		$this->orgValue = $this->eiFieldNature->getValue();
		$this->orgValueLoaded = true;
	}

	/**
	 * @param mixed $value
	 * @param bool $regardSecurity
	 * @throws InaccessibleEiFieldException
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	function setValue($value, bool $regardSecurity = true): void {
		if ($regardSecurity && !$this->getEiFieldMap()->getEiEntry()->getEiEntryAccess()
				->isEiPropWritable($this->eiPropPath)) {
			throw new InaccessibleEiFieldException('User has no write access of on field ' . $this->eiPropPath . '.');
		}
		
		$this->ensureOrgLoaded();
		$this->eiFieldNature->setValue($value);
	}
	
	/**
	 * @return mixed
	 */
	function getValue() {
		$this->ensureOrgLoaded();
		
		return $this->eiFieldNature->getValue();
	}
	
	/**
	 * @param bool $regardSecurity
	 * @return bool
	 */
	function isWritable(bool $regardSecurity) {
		return $this->eiFieldNature->isWritable()
				&& (!$regardSecurity || $this->getEiFieldMap()->getEiEntry()->getEiEntryAccess()
						->isEiPropWritable($this->eiPropPath));
	}
	/**
	 * @param EiFieldValidationResult $eiEiFieldValidationResult
	 */
	function validate(EiFieldValidationResult $eiEiFieldValidationResult) {
		$this->eiFieldNature->validate($eiEiFieldValidationResult);
	}
	
	function write() {
		$this->eiFieldNature->write();
	}
	
// 	/**
// 	 * @return \rocket\op\ei\manage\entry\EiField
// 	 */
// 	function getEiField() {
// 		return $this->eiField;
// 	}
	
	function getValidationResult(): ?ValidationResult {
		$eiEntry = $this->eiFieldMap->getEiEntry();
		
		if (!$eiEntry->hasValidationResult()) {
			return null;
		}
		
		return $eiEntry->getValidationResult()->getEiFieldValidationResult($this->eiPropPath);
	}
}
