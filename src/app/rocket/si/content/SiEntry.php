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
	private $name;
	private $treeLevel;
	/**
	 * @var SiField[]
	 */
	private $siFields;
	
	/**
	 * @param string $category
	 * @param string|null $id
	 * @param string $name
	 */
	function __construct(string $category, ?string $id, string $name) {
		$this->category = $category;
		$this->id = $id;
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @param string $category
	 * @return SiEntry
	 */
	public function setCategory(string $category) {
		$this->category = $category;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string|null $id
	 * @return SiEntry
	 */
	public function setId(?string $id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return SiEntry
	 */
	public function setName(string $name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTreeLevel() {
		return $this->treeLevel;
	}

	/**
	 * @param int|null $treeLevel
	 * @return SiEntry
	 */
	public function setTreeLevel(?int $treeLevel) {
		$this->treeLevel = $treeLevel;
	}

	/**
	 * @return SiField[]
	 */
	public function getSiFields() {
		return $this->siFields;
	}

	/**
	 * @param SiField[] $fields key is siFieldId 
	 */
	public function setSiFields(array $siFields) {
		$this->siFields = $siFields;
		return $this;
	}
	
	/**
	 * @param string $id
	 * @param SiField $siField
	 * @return \rocket\si\content\SiEntry
	 */
	function putSiField(string $id, SiField $siField) {
		$this->siFields[$id] = $siField;
		return $this;
	}
	
	public function jsonSerialize() {
		$siFieldsArr = array();
		foreach ($this->siFields as $id => $siField) {
			$siFieldsArr[$id] = [
				'type' => $siField->getType(),
				'data' => $siField->getData()
			];
		}
		
		return [
			'category' => $this->category,
			'id' => $this->id,
			'name' => $this->name,
			'treeLevel' => $this->treeLevel,
			'siFields' => $siFieldsArr
		];
	}

}
