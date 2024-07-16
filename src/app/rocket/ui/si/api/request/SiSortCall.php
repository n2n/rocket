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
namespace rocket\ui\si\api\request;
use n2n\util\type\attrs\DataMap;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\attrs\AttributesException;

class SiSortCall {

	function __construct(private string $maskId, private array $entryIds, private ?string $afterEntryId = null,
			private ?string $beforeEntryId = null, private ?string $parentEntryId = null) {
	}

	public function getMaskId(): string {
		return $this->maskId;
	}

	public function getEntryIds(): array {
		return $this->entryIds;
	}

	public function getAfterEntryId(): ?string {
		return $this->afterEntryId;
	}

	public function getBeforeEntryId(): ?string {
		return $this->beforeEntryId;
	}

	public function getParentEntryId(): ?string {
		return $this->parentEntryId;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(?array $data): ?SiSortCall {
		if ($data === null) {
			return null;
		}

		$dataMap = new DataMap($data);

		try {
			return new SiSortCall($dataMap->reqString('maskId'),
					$dataMap->reqArray('entryIds', 'string'),
					$dataMap->optString('afterId'),
					$dataMap->optString('beforeId'),
					$dataMap->optString('parentId'));
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException('Could not parse SiSortCall.', previous: $e);
		}
	}
}