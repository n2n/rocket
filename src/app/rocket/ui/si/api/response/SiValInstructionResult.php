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
namespace rocket\ui\si\api\response;

use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\content\SiValueBoundary;
use n2n\util\type\ArgUtils;

class SiValInstructionResult implements \JsonSerializable {
	/**
	 * @var bool
	 */
	private $valid;
	/** 
	 * @var SiValGetInstructionResult[]
	 */
	private $getResults = [];

	function __construct(bool $valid) {
		$this->valid = $valid;
	}
	
	/** 
	 * @return SiValGetInstructionResult[]
	 */
	function getGetResults() {
		return $this->getResults;
	}

	/**
	 * @param SiValGetInstructionResult[]
	 */
	function setGetResults(array $getResults) {
		ArgUtils::valArray($getResults, SiValGetInstructionResult::class);
		$this->getResults = $getResults;
	}
	
	/**
	 * @param string $key
	 * @param SiValGetInstructionResult $getResult
	 */
	function putGetResult(string $key, SiValGetInstructionResult $getResult) {
		$this->getResults[$key] = $getResult;
	}

	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	public function jsonSerialize(): mixed {
		return [
			'valid' => $this->valid,
			'getResults' => $this->getResults
		];
	}
}

