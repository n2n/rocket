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
namespace rocket\ui\si\content\impl;

use n2n\core\container\N2nContext;
use n2n\util\uri\Url;
use n2n\web\ui\UiComponent;
use rocket\ui\si\content\impl\iframe\IframeOutSiField;
use rocket\ui\si\content\impl\iframe\IframeInSiField;
use rocket\ui\si\content\impl\relation\QualifierSelectInSiField;
use rocket\ui\si\content\impl\relation\EmbeddedEntriesInSiField;
use rocket\ui\si\content\impl\relation\EmbeddedEntryPanelsInSiField;
use rocket\ui\si\content\impl\relation\EmbeddedEntryPanelInputHandler;
use rocket\ui\si\content\impl\relation\EmbeddedEntryInputHandler;
use rocket\ui\si\content\impl\split\SplitContextInSiField;
use rocket\ui\si\content\impl\split\SplitContextOutSiField;
use rocket\ui\si\content\impl\split\SplitPlaceholderSiField;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\control\SiNavPoint;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\content\impl\string\CkeInSiField;
use rocket\ui\si\content\impl\meta\SiCrumb;
use rocket\ui\si\content\impl\meta\CrumbOutSiField;
use rocket\ui\si\content\impl\relation\EmbeddedEntriesOutSiField;
use rocket\ui\si\content\impl\relation\EmbeddedEntryPanelsOutSiField;
use rocket\ui\si\content\impl\iframe\IframeData;
use rocket\ui\si\content\impl\date\DateTimeInSiField;
use rocket\ui\si\content\impl\string\StringArrayInSiField;
use rocket\ui\si\content\impl\string\PasswordInSiField;

class SiFields {
	
	/**
	 * @return \rocket\ui\si\content\impl\StringInSiField
	 */
	static function stringIn(?string $value) {
		return new StringInSiField($value);
	}

	static function stringOut(?string $value): StringOutSiField {
		return new StringOutSiField($value);
	}

	static function numberIn(?float $value): NumberInSiField {
		return new NumberInSiField($value);
	}
	
	/**
	 * @param bool $value
	 * @return BoolInSiField
	 */
	static function boolIn(bool $value): BoolInSiField {
		return new BoolInSiField($value);
	}
	
	/**
	 * @return CkeInSiField
	 */
	static function ckeIn(?string $value): CkeInSiField {
		return new CkeInSiField($value);
	}

	/**
	 * @param string[] $options
	 * @param string|null $value
	 * @return EnumInSiField
	 */
	static function enumIn(array $options, ?string $value) {
		return new EnumInSiField($options, $value);
	}

	static function dateTimeIn(?\DateTime $value): DateTimeInSiField {
		return new DateTimeInSiField($value);
	}

	static function fileIn(?SiFile $file, Url $apiFieldUrl, \JsonSerializable $apiCallId, SiFileHandler $fileHandle): FileInSiField {
		return new FileInSiField($file, $apiFieldUrl, $apiCallId, $fileHandle);
	}

	/**
	 * @param SiFile|null $file
	 * @return FileOutSiField
	 */
	static function fileOut(?SiFile $file): FileOutSiField {
		return new FileOutSiField($file);
	}

	/**
	 * @param SiNavPoint $navPoint
	 * @param string $label
	 * @return LinkOutSiField
	 */
	static function linkOut(SiNavPoint $navPoint, string $label): LinkOutSiField {
		return new LinkOutSiField($navPoint, $label);
	}

	/**
	 * @param SiFrame $frame
	 * @param array $values
	 * @param int $min
	 * @param int|null $max
	 * @param SiEntryQualifier[]|null $pickables
	 * @return QualifierSelectInSiField
	 */
	static function qualifierSelectIn(SiFrame $frame, array $values = [], int $min = 0, int $max = null, array $pickables = null): QualifierSelectInSiField {
		return (new QualifierSelectInSiField($frame, $values))->setMin($min)->setMax($max)->setPickables($pickables);
	}

	/**
	 * @param SiFrame $frame
	 * @param array $values
	 * @return \rocket\ui\si\content\impl\relation\EmbeddedEntriesOutSiField
	 */
	static function embeddedEntriesOut(SiFrame $frame, array $values = []) {
		return new EmbeddedEntriesOutSiField($frame, $values);
	}

	/**
	 * @param SiFrame $frame
	 * @param EmbeddedEntryInputHandler $inputHandler
	 * @param array $values
	 * @param int $min
	 * @param int|null $max
	 * @return EmbeddedEntriesInSiField
	 */
	static function embeddedEntriesIn(SiFrame $frame, EmbeddedEntryInputHandler $inputHandler, array $values = [], 
			int $min = 0, int $max = null): EmbeddedEntriesInSiField {
		return (new EmbeddedEntriesInSiField($frame, $inputHandler, $values))->setMin($min)->setMax($max);
	}
	
	/**
	 * @param SiFrame $frame
	 * @param array $panels
	 * @return EmbeddedEntryPanelsInSiField
	 */
	static function embeddedEntryPanelsOut(SiFrame $frame, array $panels = []): EmbeddedEntryPanelsOutSiField|EmbeddedEntryPanelsInSiField {
		return (new EmbeddedEntryPanelsOutSiField($frame, $panels));
	}

	/**
	 * @param SiFrame $frame
	 * @param EmbeddedEntryPanelInputHandler $inputHandler
	 * @param array $panels
	 * @return EmbeddedEntryPanelsInSiField
	 */
	static function embeddedEntryPanelsIn(SiFrame $frame, EmbeddedEntryPanelInputHandler $inputHandler, 
			array $panels = []): EmbeddedEntryPanelsInSiField {
		return (new EmbeddedEntryPanelsInSiField($frame, $inputHandler, $panels));
	}

	/**
	 * @param SiDeclaration|null $declaration
	 * @return SplitContextInSiField
	 */
	static function splitInContext(?SiDeclaration $declaration, ?SiFrame $siFrame): SplitContextInSiField {
		return new SplitContextInSiField($declaration, $siFrame);
	}

	static function splitOutContext(?SiDeclaration $declaration, ?SiFrame $siFrame): SplitContextOutSiField {
		return new SplitContextOutSiField($declaration, $siFrame);
	}

	static function splitPlaceholder(string $refPropId): SplitPlaceholderSiField {
		return new SplitPlaceholderSiField($refPropId);
	}
	
	static function crumbOut(SiCrumb ...$crumbs): CrumbOutSiField {
		$siField = new CrumbOutSiField();
		if (!empty($crumbs)) {
			$siField->addNewGroup($crumbs);
		}
		return $siField;
	}

	/**
	 * @param UiComponent $uiComponent
	 * @param N2nContext $templateN2nContext
	 * @return \rocket\ui\si\content\impl\iframe\IframeOutSiField
	 */
	static function iframeOut(UiComponent $uiComponent, N2nContext $templateN2nContext = null) {
		return new IframeOutSiField($templateN2nContext === null 
				? IframeData::createFromUiComponent($uiComponent)
				: IframeData::createFromUiComponentWithTemplate($uiComponent, $templateN2nContext));
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ui\si\content\impl\iframe\IframeOutSiField
	 */
	static function iframeUrlOut(Url $url) {
		return new IframeOutSiField(IframeData::createFromUrl($url));
	}

	/**
	 * @param UiComponent $uiComponent
	 * @param N2nContext $templateN2nContext
	 * @return \rocket\ui\si\content\impl\iframe\IframeInSiField
	 */
	static function iframeIn(UiComponent $uiComponent, N2nContext $templateN2nContext = null) {
		return new IframeInSiField($templateN2nContext === null
				? IframeData::createFromUiComponent($uiComponent)
				: IframeData::createFromUiComponentWithTemplate($uiComponent, $templateN2nContext));
	}
	
	/**
	 * @return StringArrayInSiField
	 */
	static function stringArrayIn(array $values) {
		return new StringArrayInSiField($values);
	}
	
	/**
	 * @return \rocket\ui\si\content\impl\string\PasswordInSiField
	 */
	static function passwordIn() {
		return new PasswordInSiField();
	}
}
