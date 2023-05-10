<?php

namespace rocket\impl\cu\gui\field;

use rocket\si\content\impl\SiFields;
use rocket\impl\cu\gui\field\string\StringInCuField;
use rocket\impl\cu\gui\field\date\DateTimeInCuField;
use rocket\impl\cu\gui\field\number\NumberInCuField;

class CuFields {

	static function stringIn(bool $mandatory, bool $multiline = false, int $minlength = 0, int $maxlength = 255): StringInCuField {
		return new StringInCuField(SiFields::stringIn(null)
				->setMandatory($mandatory)
				->setMultiline($multiline)
				->setMinlength($minlength)
				->setMaxlength($maxlength));
	}

	static function dateTimeIn(bool $mandatory, bool $dateChoosable = true, bool $timeChoosable = true): DateTimeInCuField {
		return new DateTimeInCuField(SiFields::dateTimeIn(null)
				->setMandatory($mandatory)
				->setDateChoosable($dateChoosable)
				->setTimeChoosable($timeChoosable));
	}

	static function numberIn(bool $mandatory = false, float $min = null, float $max = null, float $step = 1, bool $fixed = false,
			float $arrowStep = 1): NumberInCuField {
		return new NumberInCuField(SiFields::numberIn(null)
				->setMandatory($mandatory)
				->setMin($min)
				->setMax($max)
				->setStep($step)
				->setFixed($fixed)
				->setArrowStep($arrowStep));
	}
}