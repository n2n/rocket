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

use n2n\util\type\attrs\DataSet;

class SiEntryIdentifier implements \JsonSerializable {
	private $typeCategory;
	private $id;
	
	function __construct(string $category, ?string $id) {
		$this->typeCategory = $category;
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	function getCategory() {
		return $this->typeCategory;
	}
	
	/**
	 * @param string $category
	 * @return SiEntryQualifier
	 */
	function setCategory(string $category) {
		$this->typeCategory = $category;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getId() {
		return $this->id;
	}
	
	/**
	 * @param string|null $id
	 * @return SiEntryQualifier
	 */
	function setId(?string $id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return \rocket\si\content\SiEntryQualifier
	 */
	function toQualifier(?string $idName) {
		return new SiEntryQualifier($this->typeCategory, $this->id, $idName);
	}
	
	function jsonSerialize() {
		return [
			'typeCategory' => $this->typeCategory,
			'id' => $this->id,
		];
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \rocket\si\content\SiEntryIdentifier
	 */
	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new SiEntryIdentifier($ds->reqString('typeCategory'), $ds->optString('id'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}