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
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\si\meta\SiMaskIdentifier;

class SiEntryQualifier implements \JsonSerializable {
	private SiEntryIdentifier $identifier;
	
	function __construct(private SiMaskIdentifier $maskIdentifier, ?string $id, private ?string $idName = null) {
		$this->maskIdentifier = $maskIdentifier;
		$this->identifier = new SiEntryIdentifier($maskIdentifier, $id);
	}

	/**
	 * @return \rocket\ui\si\content\SiEntryIdentifier
	 */
	function getIdentifier() {
		return $this->identifier;
	}

	function getIdName(): ?string {
		return $this->idName;
	}

	function jsonSerialize(): mixed {
		return [
			'maskQualifier' => $this->maskIdentifier,
			'identifier' => $this->identifier,
			'idName' => $this->idName
		];
	}

	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			$identifier = SiEntryIdentifier::parse($ds->reqArray('identifier'));
			return new SiEntryQualifier(SiMaskQualifier::parse($ds->reqArray('maskQualifier')), 
					$identifier->getId(), $ds->optString('idName'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}