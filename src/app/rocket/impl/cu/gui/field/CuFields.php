<?php

namespace rocket\impl\cu\gui\field;

use rocket\si\content\impl\SiFields;
use rocket\impl\cu\gui\field\string\StringInCuField;
use rocket\impl\cu\gui\field\date\DateTimeInCuField;
use rocket\impl\cu\gui\field\number\NumberInCuField;
use rocket\impl\cu\gui\field\enum\EnumInCuField;

class CuFields {

	static function stringIn(bool $mandatory, bool $multiline = false, int $minlength = 0, int $maxlength = 255,
			array $prefixAddons = [], array $suffixAddons = []): StringInCuField {
		return new StringInCuField(SiFields::stringIn(null)
				->setMandatory($mandatory)
				->setMultiline($multiline)
				->setMinlength($minlength)
				->setMaxlength($maxlength)
				->setPrefixAddons($prefixAddons)
				->setSuffixAddons($suffixAddons));
	}

	static function dateTimeIn(bool $mandatory, bool $dateChoosable = true, bool $timeChoosable = true): DateTimeInCuField {
		return new DateTimeInCuField(SiFields::dateTimeIn(null)
				->setMandatory($mandatory)
				->setDateChoosable($dateChoosable)
				->setTimeChoosable($timeChoosable));
	}

	static function numberIn(bool $mandatory = false, float $min = null, float $max = null, float $step = 1, bool $fixed = false,
			float $arrowStep = 1, array $prefixAddons = [], array $suffixAddons = []): NumberInCuField {
		return new NumberInCuField(SiFields::numberIn(null)
				->setMandatory($mandatory)
				->setMin($min)
				->setMax($max)
				->setStep($step)
				->setFixed($fixed)
				->setArrowStep($arrowStep)
				->setPrefixAddons($prefixAddons)
				->setSuffixAddons($suffixAddons));
	}

	static function enumIn(bool $mandatory = false, array $options = [], string $defaultValue = null,
			string $emptyLabel = null, array $associatedPropIdsMap = []): EnumInCuField {
		return new EnumInCuField(SiFields::enumIn($options, $defaultValue)
				->setEmptyLabel($emptyLabel)
				->setMandatory($mandatory)
				->setAssociatedPropIdsMap($associatedPropIdsMap));
	}
}