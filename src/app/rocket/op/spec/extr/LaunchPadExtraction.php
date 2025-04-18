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
namespace rocket\op\spec\extr;

use rocket\op\spec\TypePath;

class LaunchPadExtraction {
	private $typePath;
	private $moduleNamespace;
	private $label;
	
	public function __construct(TypePath $typePath, string $moduleNamespace, ?string $label = null) {
		$this->typePath = $typePath;
		$this->moduleNamespace = $moduleNamespace;
		$this->label = $label;
	}
	
	public function getTypePath() {
		return $this->typePath;
	}
	
	public function setTypePath(TypePath $typePath) {
		$this->typePath = $typePath;
	}
	
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace(string $moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel(?string $label) {
		$this->label = $label;
	}
}
