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
use rocket\si\meta\SiMaskQualifier;

class SiEntryIdentifier implements \JsonSerializable {
	private $typeId;
	private $entryId;
	private $id;
	
	function __construct(string $typeId, ?string $entryId, ?string $id) {
		$this->typeId = $typeId;
		$this->entryId = $entryId;
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	function getTypeId() {
		return $this->typeId;
	}
	
	/**
	 * @param string $category
	 * @return SiEntryQualifier
	 */
	function setTypeId(string $typeId) {
		$this->typeId = $typeId;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getEntryId() {
		return $this->entryId;
	}
	
	/**
	 * @param string|null $entryId
	 * @return SiEntryQualifier
	 */
	function setEntryId(?string $entryId) {
		$this->entryId = $entryId;
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
	function toQualifier(SiMaskQualifier $maskQualifier, ?string $idName) {
		return new SiEntryQualifier($maskQualifier, $this->id, $idName);
	}
	
	function jsonSerialize(): mixed {
		return [
			'typeId' => $this->typeId,
			'entry' => $this->entryId,
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
			return new SiEntryIdentifier($ds->reqString('typeId'), $ds->optString('entryId'), $ds->optString('id'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}