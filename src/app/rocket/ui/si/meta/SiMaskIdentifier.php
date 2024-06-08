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
    protected $id;
    protected $typeId;
	
	function __construct(string $id, string $typeId) {
		$this->id = $id;
		$this->typeId = $typeId;
	}
	
	/**
	 * @return string
	 */
	function getId() {
		return $this->id;
	}

	function setId(string $id): static {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return string
	 */
	function getTypeId() {
		return $this->typeId;
	}
	
	/**
	 * @param string $typeId
	 * @return \rocket\si\meta\SiMaskIdentifier
	 */
	function setTypeId(string $typeId) {
		$this->typeId = $typeId;
		return $this;
	}
	
	function jsonSerialize(): mixed {
		return [
		    'id' => $this->id,
			'typeId' => $this->typeId
		];
	}

	/**
	 * @param array $data
	 * @return SiMaskIdentifier
	 * @throws AttributesException
	 */
	static function parse(array $data): SiMaskIdentifier {
		$ds = new DataSet($data);
		
		return new SiMaskIdentifier($ds->reqString('id'), $ds->reqString('typeId'));

	}
}