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

class SiEntryQualifier extends SiMaskQualifier {
	
	function __construct(private SiEntryIdentifier $identifier, private ?string $entryName,
			string $maskName, string $iconClass) {
		parent::__construct($this->identifier, $maskName, $iconClass);
	}

	function getIdentifier(): SiEntryIdentifier {
		return $this->identifier;
	}

	function getEntryName(): ?string {
		return $this->entryName;
	}

	function jsonSerialize(): mixed {
		return [
			...parent::jsonSerialize(),
			'entryIdentifier' => $this->identifier,
			'idName' => $this->entryName
		];
	}

	static function parse(array $data): SiMaskQualifier {
		$ds = new DataSet($data);
		
		try {
			return new SiEntryQualifier(SiEntryIdentifier::parse($ds->reqArray('identifier')),
					$ds->optString('idName'), $ds->reqString('name'), $ds->reqString('iconClass'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}