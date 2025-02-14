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
use rocket\ui\gui\field\impl\file\FileInGuiField;
use rocket\ui\si\content\impl\FileOutSiField;
use n2n\io\managed\File;
use rocket\ui\gui\field\impl\file\GuiSiFileHandler;
use rocket\ui\gui\field\impl\file\GuiSiFileFactory;
use rocket\ui\gui\field\impl\file\GuiFileVerificator;
use rocket\ui\gui\field\impl\enum\EnumInGuiField;
use rocket\ui\gui\field\impl\date\DateTimeInGuiField;
use rocket\ui\gui\field\impl\relation\GuiEmbeddedEntryFactory;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\gui\field\impl\relation\EmbeddedEntriesInGuiField;
use rocket\ui\gui\field\impl\relation\EmbeddedEntriesOutGuiField;
use rocket\ui\gui\field\impl\relation\ObjectQualifiersSelectInGuiField;
use rocket\ui\gui\field\impl\relation\EmbeddedEntryPanelsInGuiField;
use rocket\ui\gui\field\impl\relation\EmbeddedEntryPanelsOutGuiField;

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

	static function dateTimeIn(bool $mandatory, bool $dateChoosable = true, bool $timeChoosable = true): DateTimeInGuiField {
		return new DateTimeInGuiField(SiFields::dateTimeIn(null)
				->setMandatory($mandatory)
				->setDateChoosable($dateChoosable)
				->setTimeChoosable($timeChoosable));
	}

	static function numberIn(bool $mandatory = false, ?float $min = null, ?float $max = null, float $step = 1, bool $fixed = false,
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

	static function enumIn(bool $mandatory = false, array $options = [], ?string $defaultValue = null,
			?string $emptyLabel = null, array $associatedPropIdsMap = []): EnumInGuiField {
		return new EnumInGuiField(SiFields::enumIn($options, $defaultValue)
				->setEmptyLabel($emptyLabel)
				->setMandatory($mandatory)
				->setAssociatedPropIdsMap($associatedPropIdsMap));
	}

	static function fileOut(?File $file): OutGuiField {
		return new OutGuiField(SiFields::fileOut($file, new GuiSiFileHandler(new GuiSiFileFactory(), new GuiFileVerificator())));
	}

	static function fileIn(bool $mandatory = false, ?int $maxSize = null, ?array $allowedExtensions = null,
			?array $allowedMimeTypes = null): FileInGuiField {
		return new FileInGuiField(SiFields::fileIn(null, new GuiSiFileHandler(new GuiSiFileFactory(), new GuiFileVerificator()))
				->setMandatory($mandatory)
				->setMaxSize($maxSize)
				->setAcceptedExtensions($allowedExtensions ?? [])
				->setAcceptedMimeTypes($allowedMimeTypes ?? []));
	}

	static function guiEmbeddedEntriesIn(SiFrame $siFrame, GuiEmbeddedEntryFactory $embeddedEntryFactory,
			string $bulkySiMaskId, ?string $summarySiMaskId = null, bool $nonNewRemovable = true, bool $sortable = true, int $min = 0, ?int $max = null): EmbeddedEntriesInGuiField {
		$guiField = new EmbeddedEntriesInGuiField($siFrame, $embeddedEntryFactory, $bulkySiMaskId);

		$guiField->getSiField()
				->setSummaryMaskId($summarySiMaskId)
				->setNonNewRemovable($nonNewRemovable)
				->setSortable($sortable)
				->setMin($min)
				->setMax($max);

		return $guiField;
	}

	static function guiEmbeddedEntriesOut(SiFrame $siFrame,
			bool $reduced = false, array $guiEmbeddedEntries = []): EmbeddedEntriesOutGuiField {
		$guiField = new EmbeddedEntriesOutGuiField($siFrame);
		$guiField->setValue($guiEmbeddedEntries);

		$guiField->getSiField()
				->setReduced($reduced);

		return $guiField;
	}

	static function embeddedEntriesPanelsIn(SiFrame $siFrame): EmbeddedEntryPanelsInGuiField {
		return new EmbeddedEntryPanelsInGuiField($siFrame);
	}

	static function embeddedEntriesPanelsOut(SiFrame $siFrame): EmbeddedEntryPanelsOutGuiField {
		return new EmbeddedEntryPanelsOutGuiField($siFrame);
	}

	static function objectQualifiersSelectIn(SiFrame $siFrame, string $siMaskId, int $min = 0, ?int $max = null,
			?array $pickables = null): ObjectQualifiersSelectInGuiField {
		$siField = SiFields::objectQualifiersSelectIn($siFrame, $siMaskId, [], $min, $max, $pickables);

		return new ObjectQualifiersSelectInGuiField($siField);
	}
}
