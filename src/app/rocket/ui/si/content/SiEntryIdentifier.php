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
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\meta\SiMaskIdentifier;

class SiEntryIdentifier implements \JsonSerializable {
	
	function __construct(private readonly SiMaskIdentifier $maskIdentifier, private ?string $id) {
	}
	
	/**
	 * @return SiMaskIdentifier
	 */
	function getMaskIdentifier(): SiMaskIdentifier {
		return $this->maskIdentifier;
	}

	
	/**
	 * @return string|null
	 */
	function getId(): ?string {
		return $this->id;
	}

//	function setId(?string $id): static {
//		$this->id = $id;
//		return $this;
//	}
	

	function toQualifier(?string $idName): SiEntryQualifier {
		return new SiEntryQualifier($this->maskIdentifier, $this->id, $idName);
	}
	
	function jsonSerialize(): array {
		return [
			'maskIdentifier' => $this->maskIdentifier,
			'id' => $this->id,
		];
	}
	
	/**
	 * @param array $data
	 * @throws AttributesException
	 * @return SiEntryIdentifier
	 */
	static function parse(array $data): SiEntryIdentifier {
		$ds = new DataSet($data);
		
		return new SiEntryIdentifier(SiMaskIdentifier::parse($ds->reqArray('maskIdentifier')), $ds->optString('id'));
	}
}