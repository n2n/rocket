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
namespace rocket\si\meta;

use n2n\util\type\attrs\DataSet;

class SiTypeQualifier implements \JsonSerializable {
	private $category;
    private $id;
	private $name;
	private $iconClass;
	
	function __construct(string $category, string $id, string $name, string $iconClass) {
		$this->category = $category;
		$this->id = $id;
		$this->name = $name;
		$this->iconClass = $iconClass;
	}
	
	function getCategory() {
		return $this->category;
	}
	
	/**
	 * @return string
	 */
	function getId() {
		return $this->id;
	}
	
	/**
	 * @param string $id
	 * @return \rocket\si\meta\SiTypeQualifier
	 */
	function setId(string $id) {
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
	 * @return \rocket\si\meta\SiTypeQualifier
	 */
	function setName(string $name) {
		$this->name = $name;
		return $this;
	}
	
	function jsonSerialize() {
		return [
			'category' => $this->category,
		    'id' => $this->id,
			'name' => $this->name,
			'iconClass' => $this->iconClass
		];
	}

	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new SiTypeQualifier($ds->reqString('category'), $ds->reqString('id'), $ds->reqString('name'), $ds->reqString('iconClass'));
		} catch (\n2n\util\type\attrs\DataSetException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}