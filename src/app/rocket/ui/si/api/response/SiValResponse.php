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

use n2n\util\type\ArgUtils;
use n2n\core\container\N2nContext;

class SiValResponse {
	/**
	 * @var SiValInstructionResult[]
	 */
	private array $instructionResults = [];
	
	/**
	 * @return SiValInstructionResult[]
	 */
	function getInstructionResults(): array {
		return $this->instructionResults;
	}

	/**
	 * @param SiValInstructionResult[] $instructionResults
	 * @return $this
	 */
	function setInstructionResults(array $instructionResults): static {
		ArgUtils::valArray($instructionResults, SiValInstructionResult::class);
		$this->instructionResults = $instructionResults;
		return $this;
	}
	
	/** 
	 * @param string $key
	 * @param SiValInstructionResult $instructionResult
	 */
	function putInstructionResult(string $key, SiValInstructionResult $instructionResult): void {
		$this->instructionResults[$key] = $instructionResult;
	}

	/**
	 * @param N2nContext $n2nContext
	 * @return array
	 */
	public function toJsonStruct(N2nContext $n2nContext): array {
		return ['instructionResults' => array_map(
				fn (SiValInstructionResult $r) => $r->toJsonStruct($n2nContext),
				$this->instructionResults)];
	}	
}
