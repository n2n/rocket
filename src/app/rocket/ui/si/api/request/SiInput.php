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

use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\attrs\DataMap;

class SiInput implements \JsonSerializable {
	/**
	 * @var SiValueBoundaryInput[] $valueBoundaryInputs
	 */
	protected array $valueBoundaryInputs = [];
	
	/**
	 * @return SiValueBoundaryInput[];
	 */
	function getValueBoundaryInputs(): array {
		return $this->valueBoundaryInputs;
	}
	
	/**
	 * @param string $key
	 * @param SiValueBoundaryInput $valueBoundaryInput
	 */
	function putValueBoundaryInput(string $key, SiValueBoundaryInput $valueBoundaryInput): void {
		$this->valueBoundaryInputs[$key] = $valueBoundaryInput;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(array $data): SiInput {
		$input = new SiInput();

		$dataMap = new DataMap($data);
		foreach ($data as $key => $entryData) {
			try {
				$input->putValueBoundaryInput($key, SiValueBoundaryInput::parse($dataMap->reqArray($key)));
			} catch (AttributesException $e) {
				throw new CorruptedSiDataException(null, 0, $e);
			}
		}

		return $input;
	}

	function jsonSerialize(): array {
		return $this->valueBoundaryInputs;
	}
}