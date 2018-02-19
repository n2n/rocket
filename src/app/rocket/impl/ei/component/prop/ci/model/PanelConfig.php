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
namespace rocket\impl\ei\component\prop\ci\model;

use n2n\util\ex\IllegalStateException;

class PanelConfig {
	private $name;
	private $label;
	private $allowedContentItemIds;
	private $min;
	private $max;
	private $gridPos;
	
	/**
	 * @param string $name
	 * @param string $label
	 * @param string[]|null $allowedContentItemIds
	 * @param int $min
	 * @param int|null $max
	 * @param GridPos|null $gridPos
	 */
	public function __construct(string $name, string $label, array $allowedContentItemIds = null,
			int $min = 0, int $max = null, GridPos $gridPos = null) {
		$this->name = $name;
		$this->label = $label;
		$this->allowedContentItemIds = $allowedContentItemIds;
		$this->min = $min;
		$this->max = $max;
		$this->gridPos = $gridPos;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 */
	public function setLabel(string $label) {
		$this->label = $label;
	}
	
	/**
	 * @return bool
	 */
	public function isRestricted() {
		return $this->allowedContentItemIds !== null;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return string[]
	 */
	public function getAllowedContentItemIds() {
		if ($this->allowedContentItemIds === null) {
			throw new IllegalStateException('Panel is unrestricted.');
		}
		return $this->allowedContentItemIds;
	}
	
	/**
	 * @param string[] $allowedContentItemIds
	 */
	public function setAllowedContentItemIds(array $allowedContentItemIds = null) {
		$this->allowedContentItemIds = $allowedContentItemIds;
	}
	
	/**
	 * @return int
	 */
	public function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int $min
	 */
	public function setMin(int $min) {
		$this->min = $min;
	}
	
	/**
	 * @return int|null
	 */
	public function getMax() {
		return $this->max;
	}
	
	/**
	 * @param int|null $max
	 */
	public function setMax(?int $max) {
		$this->max = $max;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\ci\model\GridPos|null
	 */
	public function getGridPos() {
		return $this->gridPos;
	}
	
	/**
	 * @param \rocket\impl\ei\component\prop\ci\model\GridPos|null $gridPos
	 */
	public function setGridPos(?GridPos $gridPos) {
		$this->gridPos = $gridPos;
	}
	
}

class GridPos {
	private $colStart;
	private $colEnd;
	private $rowStart;
	private $rowEnd;
	
	/**
	 * @param int $colStart
	 * @param int $colEnd
	 * @param int $rowStart
	 * @param int $rowEnd
	 */
	public function __construct(int $colStart, ?int $colEnd, int $rowStart, ?int $rowEnd) {
		$this->colStart = $colStart;
		$this->colEnd = $colEnd ?? $colStart + 1;
		$this->rowStart = $rowStart;
		$this->rowEnd = $rowEnd ?? $rowStart + 1;
		
		if ($this->colStart < 1) {
			$this->colStart = 1;
		}
		
		if ($this->rowStart < 1) {
			$this->rowStart = 1;
		}
		
		if ($this->colStart >= $this->colEnd) {
			$this->colEnd = $this->colStart + 1;
		}
		
		if ($this->rowStart > $this->rowEnd) {
			$this->rowEnd = $this->rowStart + 1;
		}
	}
	
	/**
	 * @return int
	 */
	public function getColStart() {
		return $this->colStart;
	}
	
	/**
	 * @param int $colStart
	 */
	public function setColStart(int $colStart) {
		$this->colStart = $colStart;
	}
	
	/**
	 * @return int
	 */
	public function getColEnd() {
		return $this->colEnd;
	}
	
	/**
	 * @param int $colEnd
	 */
	public function setColEnd(int $colEnd) {
		$this->colEnd = $colEnd;
	}
	
	/**
	 * @return int
	 */
	public function getRowStart() {
		return $this->rowStart;
	}
	
	/**
	 * @param int $rowStart
	 */
	public function setRowStart(int $rowStart) {
		$this->rowStart = $rowStart;
	}
	
	/**
	 * @return int
	 */
	public function getRowEnd() {
		return $this->rowEnd;
	}
	
	/**
	 * @param int $rowEnd
	 */
	public function setRowEnd(int $rowEnd) {
		$this->rowEnd = $rowEnd;
	}
}