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

use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\attrs\DataMap;
use n2n\util\type\attrs\AttributesException;

class SiControlCall implements \JsonSerializable {

	/**
	 * @param string|null $maskId
	 * @param string|null $entryId if not null the call is meant for an entry control
	 * @param string $controlName
	 */
	function __construct(private ?string $maskId, private ?string $entryId, private string $controlName) {
	}

	function getMaskId(): ?string {
		return $this->maskId;
	}

	function getEntryId(): ?string {
		return $this->entryId;
	}

	function getControlName(): string {
		return $this->controlName;
	}

	function jsonSerialize(): mixed {
		return [
			'maskId' => $this->maskId,
			'entryId' => $this->entryId,
			'controlName' => $this->controlName
		];
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(?array $data): ?SiControlCall {
		if ($data === null) {
			return null;
		}

		$dataMap = new DataMap($data);
		try {
			return new SiControlCall($dataMap->optString('maskId'), $dataMap->optString('entryId'),
					$dataMap->reqString('controlName'));
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException(previous: $e);
		}
	}
}