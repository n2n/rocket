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

namespace testmdl\string\bo;

use n2n\spec\valobj\scalar\StringValueObject;
use n2n\spec\valobj\err\IllegalValueException;
use n2n\bind\attribute\impl\Marshal;
use n2n\bind\mapper\impl\Mappers;
use n2n\validation\validator\impl\Validators;
use n2n\bind\attribute\impl\Unmarshal;
use n2n\bind\mapper\Mapper;

class StrObjMock implements StringValueObject {

	public function __construct(private readonly string $value) {
		IllegalValueException::assertTrue(mb_strlen($value) <= 7);
	}

	#[Marshal]
	static function marshal(): Mapper {
		return Mappers::valueClosure(fn (StringValueObject $mock) => $mock->toScalar());
	}

	#[Unmarshal]
	static function unmarshal(): Mapper {
		return Mappers::pipe(Validators::maxlength(7),
				Mappers::valueNotNullClosure(fn (string $value) => new StrObjMock($value)));
	}

	function toScalar(): string {
		return $this->value;
	}
}