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
use InvalidArgumentException;
use n2n\util\type\attrs\AttributesException;

class SiEntryIdentifier implements \JsonSerializable {
	
	function __construct(private string $typeId, private ?string $id) {
	}
	
	/**
	 * @return string
	 */
	function getTypeId(): string {
		return $this->typeId;
	}

	function setTypeId(string $typeId): static {
		$this->typeId = $typeId;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getId(): ?string {
		return $this->id;
	}

	function setId(?string $id): static {
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
			'id' => $this->id,
		];
	}
	
	/**
	 * @param array $data
	 * @throws InvalidArgumentException
	 * @return SiEntryIdentifier
	 */
	static function parse(array $data): SiEntryIdentifier {
		$ds = new DataSet($data);
		
		try {
			return new SiEntryIdentifier($ds->reqString('typeId'), $ds->optString('id'));
		} catch (AttributesException $e) {
			throw new InvalidArgumentException(null, null, $e);
		}
	}
}