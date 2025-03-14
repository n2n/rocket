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
use n2n\core\container\N2nContext;

class SiValInstructionResult {
	/**
	 * @var bool
	 */
	private $valid;
	/** 
	 * @var SiValGetInstructionResult[]
	 */
	private array $getResults = [];

	function __construct(bool $valid, private SiValueBoundary $valueBoundary) {
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
	function putGetResult(string $key, SiValGetInstructionResult $getResult): void {
		$this->getResults[$key] = $getResult;
	}

	public function toJsonStruct(N2nContext $n2nContext): array {
		return [
			'valid' => $this->valid,
			'valueBoundary' => $this->valueBoundary->toJsonStruct($n2nContext),
			'getResults' => array_map(fn (SiValGetInstructionResult $r) => $r->toJsonStruct($n2nContext), $this->getResults)
		];
	}
}

