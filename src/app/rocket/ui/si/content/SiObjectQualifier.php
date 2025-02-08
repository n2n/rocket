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
namespace rocket\ui\si\content;

use n2n\util\type\attrs\DataSet;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\meta\SiMaskQualifier;

class SiObjectQualifier implements \JsonSerializable {

	function __construct(private string $superTypeId, private string $id, private string $idName) {
	}

	function getSuperTypeId(): string {
		return $this->superTypeId;
	}

	function getId(): string {
		return $this->id;
	}

	function getIdName(): string {
		return $this->idName;
	}

	function jsonSerialize(): mixed {
		return [
			'superTypeId' => $this->superTypeId,
			'id' => $this->id,
			'idName' => $this->idName
		];
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiObjectQualifier {
		$ds = new DataSet($data);

		try {
			return new SiObjectQualifier($ds->reqArray('superTypeId'),
					$ds->reqString('id'), $ds->reqString('idName'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new CorruptedSiDataException(null, null, $e);
		}
	}
}