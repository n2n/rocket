<?php

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
		return Mappers::pipe(Validators::maxlength(5),
				Mappers::valueNotNullClosure(fn (string $value) => new StrObjMock($value)));
	}

	function toScalar(): string {
		return $this->value;
	}
}