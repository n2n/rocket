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

use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataMap;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\api\request\SiEntryInput;

class SiValueBoundaryInput implements \JsonSerializable {

	/**
	 * Ent
	 * @var string[]|null
	 */
	private ?array $maskIds = null;

	function __construct(private readonly string $selectedMaskId, private readonly SiEntryInput $entryInput) {

	}

	function getSelectedMaskId(): string {
		return $this->selectedMaskId;
	}

	function setMaskIds(?array $maskIds): static {
		ArgUtils::valArray($maskIds, 'string');
		$this->maskIds = $maskIds;
		return $this;
	}

	function getMaskIds(): ?array {
		return $this->maskIds;
	}

	function getEntryInput(): SiEntryInput {
		return $this->entryInput;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiValueBoundaryInput {
		$dataMap = new DataMap($data);

		try {
			$siValueBoundary = new SiValueBoundaryInput($dataMap->reqString('selectedMaskId'),
					SiEntryInput::parse($dataMap->reqArray('entryInput')));
//			$siValueBoundary->setMaskIds($dataMap->reqArray('maskIds', 'string', true));
			return $siValueBoundary;
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException('Could not parse SiValueBoundaryInput.', previous: $e);
		}
	}

	function jsonSerialize(): mixed {
		return [
			'selectedMaskId' => $this->selectedMaskId,
//			'maskIds' => $this->maskIds,
			'entryInput' => $this->entryInput
		];
	}

}