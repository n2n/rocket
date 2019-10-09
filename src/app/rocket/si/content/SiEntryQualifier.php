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

class SiEntryQualifier extends SiEntryIdentifier implements \JsonSerializable {
    private $type;
	private $idName;
	
	function __construct(string $typeCategory, ?string $id, string $idName = null) {
		parent::__construct($typeCategory, $id);
		$this->idName = $idName;
	}

	function jsonSerialize() {
		return [
			'category' => $this->getCategory(),
			'id' => $this->getId(),
		    'typeId' => $this->typeId,
			'type' => $this->type,
			'iconClass' => $this->iconClass,
			'idName' => $this->idName
		];
	}

	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new SiEntryQualifier($ds->reqString('typeCategory'), $ds->optString('id'), 
					$ds->optString('idName'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}