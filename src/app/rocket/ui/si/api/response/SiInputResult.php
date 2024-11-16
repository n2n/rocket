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
use rocket\ui\si\content\SiValueBoundary;
use n2n\core\container\N2nContext;

class SiInputResult {
	/**
	 * @param SiValueBoundary[] $valueBoundaries
	 * @param bool $valid
	 */
	function __construct(private readonly array $valueBoundaries, private readonly bool $valid) {
		ArgUtils::valArray($this->valueBoundaries, SiValueBoundary::class, true);
	}

	function isValid(): bool {
		return $this->valid;
	}

	function getValueBoundaries(): ?array {
		return $this->valueBoundaries;
	}

	function toJsonStruct(N2nContext $n2nContext): mixed {
		return [
			'valid' => $this->valid,
			'valueBoundaries' => array_map(
					fn (SiValueBoundary $b) => $b->toJsonStruct($n2nContext), $this->valueBoundaries)
		];
	}

	static function valid(array $valueBoundaries): SiInputResult {
		return new SiInputResult($valueBoundaries, true);
	}

	static function error(array $valueBoundaries): SiInputResult {
		return new SiInputResult($valueBoundaries, false);
	}
}