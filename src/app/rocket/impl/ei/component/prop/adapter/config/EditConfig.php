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
namespace rocket\impl\ei\component\prop\adapter\config;

use rocket\op\ei\component\prop\indepenent\PropertyAssignation;

class EditConfig {
	protected bool $constant = false;
	protected bool $readOnly = false;
	protected bool $mandatory = false;

	/**
	 * @return bool
	 */
	function isConstant(): bool {
		return $this->constant;
	}

	/**
	 * @param bool $constant
	 * @return $this
	 */
	function setConstant(bool $constant): static {
		$this->constant = $constant;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isReadOnly(): bool {
		return $this->readOnly;
	}

	/**
	 * @param bool $readOnly
	 * @return $this
	 */
	function setReadOnly(bool $readOnly): static {
		$this->readOnly = (bool) $readOnly;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isMandatory(): bool {
		return $this->mandatory;
	}

	/**
	 * @param bool $mandatory
	 * @return $this
	 */
	function setMandatory(bool $mandatory): static {
		$this->mandatory = $mandatory;
		return $this;
	}
}
