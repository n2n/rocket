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

class SiQualifier extends SiIdentifier implements \JsonSerializable {
    private $typeId;
	private $typeName;
	private $iconClass;
	private $idName;
	
	function __construct(string $category, ?string $id, string $typeId, string $typeName, string $iconClass, 
			string $idName = null) {
		parent::__construct($category, $id);
		$this->typeId = $typeId;
		$this->typeName = $typeName;
		$this->iconClass = $iconClass;
		$this->idName = $idName;
	}
	
	/**
	 * @return string
	 */
	function getTypeName() {
		return $this->typeName;
	}
	
	/**
	 * @param string $name
	 * @return SiQualifier
	 */
	function setTypeName(string $typeName) {
		$this->typeName = $typeName;
		return $this;
	}
	
	function jsonSerialize() {
		return [
			'category' => $this->getCategory(),
			'id' => $this->getId(),
		    'typeId' => $this->typeId,
			'typeName' => $this->typeName,
			'iconClass' => $this->iconClass,
			'idName' => $this->idName
		];
	}

	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new SiQualifier($ds->reqString('category'), $ds->optString('id'), $ds->reqString('typeId'),
					$ds->reqString('typeName'), $ds->reqString('iconClass'), $ds->optString('idName'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}