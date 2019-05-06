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
namespace rocket\si\content;

class SiEntry implements \JsonSerializable {
	private $category;
	private $id;
	private $buildups = [];
	private $treeLevel;
	private $inputAvailable;
	
	/**
	 * @param string $category
	 * @param string|null $id
	 * @param string $name
	 */
	function __construct(string $category, ?string $id, bool $inputAvailable) {
		$this->category = $category;
		$this->id = $id;
		$this->inputAvailable = $inputAvailable;
	}
	
	/**
	 * @return string
	 */
	function getCategory() {
		return $this->category;
	}

	/**
	 * @param string $category
	 * @return SiEntry
	 */
	function setCategory(string $category) {
		$this->category = $category;
		return $this;
	}

	/**
	 * @return string
	 */
	function getId() {
		return $this->id;
	}

	/**
	 * @param string|null $id
	 * @return SiEntry
	 */
	function setId(?string $id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return SiEntry
	 */
	function setName(string $name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return int|null
	 */
	function getTreeLevel() {
		return $this->treeLevel;
	}

	/**
	 * @param int|null $treeLevel
	 * @return SiEntry
	 */
	function setTreeLevel(?int $treeLevel) {
		$this->treeLevel = $treeLevel;
	}

	/**
	 * @return SiEntryBuildup[]
	 */
	function getBuildups() {
		return $this->buildups;
	}

	/**
	 * @param SiEntryBuildup[] $buildups 
	 */
	function setBuildups(array $buildups) {
		$this->buildups = $buildups;
		return $this;
	}
	
	/**
	 * @param string $id
	 * @param SiField $field
	 * @return \rocket\si\content\SiEntry
	 */
	function putBuildup(string $id, SiEntryBuildup $buildup) {
		$this->buildups[$id] = $buildup;
		return $this;
	}
	
	function jsonSerialize() {
		$buildups = array();
		foreach ($this->buildups as $id => $buildup) {
			$buildups[$id] = $buildup;
		}
				
		return [
			'category' => $this->category,
			'id' => $this->id,
			'treeLevel' => $this->treeLevel,
			'inputAvailable' => $this->inputAvailable,
			'buildups' => $buildups
		];
	}

}
