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
namespace rocket\ei\manage\entry;

use rocket\ei\manage\gui\EiFieldAbstraction;
use rocket\ei\EiPropPath;

class EiFieldWrapper implements EiFieldAbstraction {
	private $eiPropPath;
	private $eiField;
	private $ignored = false;
	private $validationResult;
	
	public function __construct(EiPropPath $eiPropPath, EiField $eiField) {
		$this->eiPropPath = $eiPropPath;
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
	
	/**
	 * @param EiFieldValidationResult $validationResult
	 */
	public function setValidationResult(EiFieldValidationResult $validationResult) {
		$this->validationResult = $validationResult;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiFieldValidationResult
	 */
	public function getValidationResult() {
		if ($this->validationResult === null) {
			$this->validationResult = new EiFieldValidationResult($this->eiPropPath);
		}
		
		return $this->validationResult;
	}
}