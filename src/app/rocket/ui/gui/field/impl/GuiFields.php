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
namespace rocket\ui\gui\field\impl;

use rocket\ui\si\content\impl\SiFields;
use rocket\ui\gui\field\impl\string\StringInGuiField;
use rocket\ui\si\content\BackableSiField;
use rocket\ui\gui\field\impl\general\OutGuiField;
use rocket\ui\gui\field\impl\number\NumberInGuiField;

class GuiFields {

	static function out(BackableSiField $siField): OutGuiField {
		return new OutGuiField($siField);
	}

	static function stringIn(bool $mandatory, bool $multiline = false, int $minlength = 0, int $maxlength = 255,
			array $prefixAddons = [], array $suffixAddons = []): StringInGuiField {
		return new StringInGuiField(SiFields::stringIn(null)
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
			float $arrowStep = 1, array $prefixAddons = [], array $suffixAddons = []): NumberInGuiField {
		return new NumberInGuiField(SiFields::numberIn(null)
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