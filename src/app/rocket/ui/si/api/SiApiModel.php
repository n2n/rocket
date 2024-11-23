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

namespace rocket\ui\si\api;

use rocket\ui\si\meta\SiMask;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\err\UnknownSiElementException;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\si\content\SiField;
use rocket\ui\si\api\response\SiCallResponse;

interface SiApiModel {

	/**
	 * @param string $maskId
	 * @return SiMask
	 * @throws UnknownSiElementException
	 */
	function getSiMask(string $maskId): SiMask;

	/**
	 * @param string $maskId
	 * @param string $controlName
	 * @return SiControl
	 * @throws UnknownSiElementException
	 */
	function getSiMaskControl(string $maskId, string $controlName): SiControl;

	/**
	 * @param string $maskId
	 * @param string $entryId
	 * @param string $controlName
	 * @return SiControl
	 * @throws UnknownSiElementException
	 */
	function getSiEntryControl(string $maskId, string $entryId, string $controlName): SiControl;

	/**
	 * @param string $maskId
	 * @param string|null $entryId
	 * @param string $fieldName
	 * @return SiField
	 * @throws UnknownSiElementException
	 */
	function lookupSiField(string $maskId, ?string $entryId, string $fieldName): SiField;

	/**
	 * @param string $maskId
	 * @param string|null $entryId
	 * @param array|null $allowedFieldNames
	 * @return SiValueBoundary
	 * @throws UnknownSiElementException
	 */
	function lookupSiValueBoundary(string $maskId, ?string $entryId, ?array $allowedFieldNames): SiValueBoundary;

	/**
	 * @param string $maskId
	 * @param int $from
	 * @param int $num
	 * @param string|null $quickSearchStr
	 * @param string[]|null $allowedFieldNames
	 * @return SiPartialContent
	 */
	function lookupSiPartialContent(string $maskId, int $from, int $num, ?string $quickSearchStr,
			?array $allowedFieldNames): SiPartialContent;

	/**
	 * @param SiValueBoundary $boundary
	 * @param string $maskId
	 * @return SiValueBoundary
	 * @throws UnknownSiElementException
	 */
	function copySiValueBoundary(SiValueBoundary $boundary, string $maskId): SiValueBoundary;

	/**
	 * @param string[] $entryIds
	 * @throws UnknownSiElementException
	 */
	function insertSiEntriesAfter(string $maskId, array $entryIds, string $afterEntryId): SiCallResponse;

	/**
	 * @param string[] $entryIds
	 * @throws UnknownSiElementException
	 */
	function insertSiEntriesBefore(string $maskId, array $entryIds, string $beforeEntryId): SiCallResponse;

	/**
	 * @param string[] $entryIds
	 * @throws UnknownSiElementException
	 */
	function insertSiEntriesAsChildren(string $maskId, array $entryIds, string $parentId): SiCallResponse;

}