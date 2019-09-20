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
namespace rocket\si\content\impl;

use n2n\util\type\ArgUtils;
use rocket\si\content\SiEmbeddedEntry;
use rocket\si\content\SiType;

class SiPanel implements \JsonSerializable {
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $label;
	/**
	 * @var int
	 */
	private $min = 0;
	/**
	 * @var int|null
	 */
	private $max = null;
	/**
	 * @var SiType[]
	 */
	private $allowedTypes = [];
	/**
	 * @var SiGridPos|null
	 */
	private $gridPos = null;
	/**
	 * @var SiEmbeddedEntry[]
	 */
	private $embeddedEntries = [];

	/**
	 * @param string $name
	 * @param string $label
	 */
	function __construct(string $name, string $label) {
		$this->name = $name;
		$this->label = $label;
	}
	
	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	function setName(string $name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 */
	function setLabel(string $label) {
		$this->label = $label;
	}
		
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int $min
	 */
	function setMin(int $min) {
		$this->min = $min;
	}
	
	/**
	 * @return int
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @param int|null $max
	 */
	function setMax(?int $max) {
		$this->max = $max;
	}
	
	/**
	 * @return \rocket\si\content\SiType[]
	 */
	function getAllowedTypes() {
		return $this->allowedTypes;
	}
	
	/**
	 * @param \rocket\si\content\SiType[] $allowedTypes
	 */
	function setAllowedTypes(array $allowedTypes) {
		ArgUtils::valArray($allowedTypes, SiType::class);
		$this->allowedTypes = $allowedTypes;
	}
	
	/**
	 * @return \rocket\si\content\impl\SiGridPos|null
	 */
	function getGridPos() {
		return $this->gridPos;
	}
	
	/**
	 * @param \rocket\si\content\impl\SiGridPos|null $gridPos
	 */
	function setGridPos(?SiGridPos $gridPos) {
		$this->gridPos = $gridPos;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getEmbedddedEntries() {
		return $this->embeddedEntries;
	}
	
	/**
	 * @param SiEmbeddedEntry[] $embeddedEntries
	 */
	function setEmbeddedEntries(array $embeddedEntries) {
		ArgUtils::valArray($embeddedEntries, SiEmbeddedEntry::class);
		$this->embeddedEntries = $embeddedEntries;
	}
	
	/**
	 * @param SiEmbeddedEntry $embeddedEntry
	 */
	function addEmbeddedEntry(SiEmbeddedEntry $embeddedEntry) {
		$this->embeddedEntries[] = $embeddedEntry;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'name' => $this->name,
			'label' => $this->label,
			'min' => $this->min,
			'max' => $this->max,
			'allowedSiTypes' => $this->allowedSiTypes,
			'gridPos' => $this->gridPos,
			'embeddedEntries' => $this->embeddedEntries
		];
	}
}
