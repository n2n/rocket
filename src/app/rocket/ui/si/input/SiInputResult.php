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
namespace rocket\ui\si\input;


use n2n\util\type\ArgUtils;
use rocket\ui\si\content\SiValueBoundary;

class SiInputResult {
	private function __construct(private readonly ?array $valueBoundaries, private readonly ?SiInputError $siInputError) {
		ArgUtils::valArray($this->valueBoundaries, SiValueBoundary::class, true);
	}

	function isValid(): bool {
		return $this->siInputError === null;
	}

	function getValueBoundaries(): ?array {
		return $this->valueBoundaries;
	}

	function getInputError(): ?SiInputError {
		return $this->siInputError;
	}

	static function valid(array $valueBoundaries): SiInputResult {
		return new SiInputResult($valueBoundaries, null);
	}

	static function error(SiInputError $siInputError): SiInputResult {
		return new SiInputResult(null, $siInputError);
	}
}