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
namespace rocket\ui\si\meta;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;

class SiMaskIdentifier implements \JsonSerializable {

	/**
	 * @param string $id
	 * @param string $typeId used to find a similar summary SiEntry of bulky SiEntry
	 * @param string $superTypeId
	 */
	function __construct(private string $id, private string $typeId, private string $superTypeId) {
	}
	
	/**
	 * @return string
	 */
	function getId(): string {
		return $this->id;
	}

//	function setId(string $id): static {
//		$this->id = $id;
//		return $this;
//	}
	
	/**
	 * @return string
	 */
	function getTypeId(): string {
		return $this->typeId;
	}

	function getSuperTypeId(): string {
		return $this->superTypeId;
	}

//	/**
//	 * @param string $typeId
//	 * @return \rocket\si\meta\SiMaskIdentifier
//	 */
//	function setTypeId(string $typeId) {
//		$this->typeId = $typeId;
//		return $this;
//	}
	
	function jsonSerialize(): mixed {
		return [
		    'id' => $this->id,
			'typeId' => $this->typeId,
			'superTypeId' => $this->superTypeId
		];
	}

	/**
	 * @param array $data
	 * @return SiMaskIdentifier
	 * @throws AttributesException
	 */
	static function parse(array $data): SiMaskIdentifier {
		$ds = new DataSet($data);
		
		return new SiMaskIdentifier($ds->reqString('id'), $ds->reqString('kindId'), $ds->reqString('typeId'));

	}
}