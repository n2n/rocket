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

class SiIdentifier implements \JsonSerializable {
	private $category;
	private $id;
	
	function __construct(string $category, ?string $id) {
		$this->category = $category;
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	function getCategory() {
		return $this->category;
	}
	
	/**
	 * @param string $category
	 * @return SiQualifier
	 */
	function setCategory(string $category) {
		$this->category = $category;
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
	 * @return SiQualifier
	 */
	function setId(?string $id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return \rocket\si\content\SiQualifier
	 */
	function toQualifier(string $name) {
		return new SiQualifier($this->category, $this->id, $name);
	}
	
	function jsonSerialize() {
		return [
			'category' => $this->category,
			'id' => $this->id,
		];
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \rocket\si\content\SiIdentifier
	 */
	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new SiIdentifier($ds->reqString('category'), $ds->optString('id'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}