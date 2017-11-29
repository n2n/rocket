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
namespace rocket\impl\ei\component\field\ci\model;

use n2n\util\ex\IllegalStateException;

class PanelConfig {
	private $name;
	private $label;
	private $allowedContentItemIds;
	private $min;
	private $max;
	
	public function __construct(string $name, string $label, array $allowedContentItemIds = null, 
			int $min = 0, int $max = null) {
		$this->name = $name; 
		$this->label = $label;
		$this->allowedContentItemIds = $allowedContentItemIds;
		$this->min = $min;
		$this->max = $max;
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function setName(string $name) {
		$this->name = $name;
	}
	
	public function getLabel(): string {
		return $this->label;
	}
	
	public function setLabel(string $label) {
		$this->label = $label;
	}
		
	public function isRestricted(): bool {
		return $this->allowedContentItemIds !== null;
	}
	
	public function getAllowedContentItemIds(): array {
		if ($this->allowedContentItemIds === null) {
			throw new IllegalStateException('Panel is unrestricted.');
		}
		return $this->allowedContentItemIds;
	}
	
	public function setAllowedContentItemIds(array $allowedContentItemIds = null) {
		$this->allowedContentItemIds = $allowedContentItemIds;
	}
	
	public function getMin(): int {
		return $this->min;
	}
	
	public function setMin(int $min) {
		$this->min = $min;
	}
	
	public function getMax() {
		return $this->max;
	}
	
	public function setMax(int $max = null) {
		$this->max = $max;
	}
}
