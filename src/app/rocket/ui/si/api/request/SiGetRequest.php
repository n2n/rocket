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

use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\AttributesException;
use n2n\util\type\ArgUtils;
use rocket\ui\si\err\CorruptedSiDataException;

class SiGetRequest implements \JsonSerializable {
	private $instructions = [];
	
	/**
	 * 
	 */
	function __construct() {	
	}
	
	/**
	 * @return SiGetInstruction[]
	 */
	function getInstructions() {
		return $this->instructions;
	}

	/**
	 * @param SiGetInstruction[]
	 */
	function setInstructions(array $instructions) {
		ArgUtils::valArray($instructions, SiGetInstruction::class);
		$this->instructions = $instructions;
		return $this;
	}
	
	function putInstruction(string $key, SiGetInstruction $instruction) {
		$this->instructions[$key] = $instruction;
	}

	function jsonSerialize(): mixed {
		return ['instructions' => $this->instructions];
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	static function parse(?array $data): ?SiGetRequest {
		if ($data === null) {
			return null;
		}

		$ds = new DataSet($data);
		
		$getRequest = new SiGetRequest();
		try {
			foreach ($ds->reqArray('instructions') as $key => $instructionData) {
				$getRequest->putInstruction($key, SiGetInstruction::parse($instructionData));
			}
		} catch (AttributesException $e) {
			throw new CorruptedSiDataException('Could not parse SiGetRequest.', previous: $e);
		}
		return $getRequest;
	}
}
