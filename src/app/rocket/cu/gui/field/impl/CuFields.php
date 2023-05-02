<?php

namespace rocket\cu\gui\field\impl;

use rocket\si\content\impl\SiFields;
use rocket\cu\gui\field\impl\string\StringInCuField;
use rocket\si\content\impl\date\DateTimeInSiField;
use rocket\cu\gui\field\impl\date\DateTimeInCuField;

class CuFields {

	function stringIn(bool $mandatory, bool $multiline = false, int $minlength = 0, int $maxlength = 255): StringInCuField {
		return new StringInCuField(SiFields::stringIn()
				->setMaxlength($mandatory)
				->setMultiline($multiline)
				->setMinlength($minlength)
				->setMaxlength($maxlength));
	}

	function dateTimeIn(bool $mandatory, bool $dateChoosable = true, bool $timeChoosable = true): DateTimeInCuField {
		return new DateTimeInCuField(SiFields::dateTimeIn()
				->setMandatory($mandatory)
				->setDateChoosable($dateChoosable)
				->setTimeChoosable($timeChoosable));
	}

	function numberIn(bool $mandatory = false, float $min = null, float $max = null, float $step = 1, bool $fixed = false,
			float $arrowStep = 1): NumberInCuField {
		return new NumberInCuField(SiFields::numberIn()
				->setMandatory($mandatory)
				->setMin($min)
				->setMax($max)
				->setStep($step)
				->setFixed($fixed)
				->setArrowStep($arrowStep));
	}
}