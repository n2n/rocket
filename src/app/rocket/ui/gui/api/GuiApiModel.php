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

namespace rocket\ui\gui\api;

use rocket\ui\gui\err\UnknownGuiElementException;
use rocket\ui\gui\GuiMask;
use rocket\ui\gui\GuiValueBoundary;
use rocket\ui\gui\GuiCallResponse;

interface GuiApiModel {

	/**
	 * @throws UnknownGuiElementException
	 */
	function lookupGuiMask(string $maskId): GuiMask;

	/**
	 * @throws UnknownGuiElementException
	 */
	function lookupGuiValueBoundary(string $maskId, string $entryId): GuiValueBoundary;

	/**
	 * @throws UnknownGuiElementException
	 */
	function createGuiValueBoundary(string $maskId): GuiValueBoundary;

	/**
	 * @throws UnknownGuiElementException
	 * @return GuiValueBoundary[]
	 */
	function lookupGuiValueBoundaries(string $maskId, int $offset, int $num, ?string $quickSearchStr): array;

	/**
	 * @param string $maskId
	 * @param string|null $quickSearchStr
	 * @return int
	 * @throws UnknownGuiElementException
	 */
	function countGuiValueBoundaries(string $maskId, ?string $quickSearchStr): int;

	/**
	 * @param GuiValueBoundary $guiValueBoundary
	 * @param string $maskId
	 * @return GuiValueBoundary
	 * @throws UnknownGuiElementException
	 */
	function copyGuiValueBoundary(GuiValueBoundary $guiValueBoundary, string $maskId): GuiValueBoundary;

	/**
	 * @param string $maskId
	 * @param string[] $entryIds
	 * @param string $afterEntryId
	 * @return GuiCallResponse
	 * @throws UnknownGuiElementException
	 */
	function insertAfter(string $maskId, array $entryIds, string $afterEntryId): GuiCallResponse;

	/**
	 * @param string $maskId
	 * @param string[] $entryIds
	 * @param string $beforeEntryId
	 * @return GuiCallResponse
	 * @throws UnknownGuiElementException
	 */
	function insertBefore(string $maskId, array $entryIds, string $beforeEntryId): GuiCallResponse;

	/**
	 * @param string $maskId
	 * @param string[] $entryIds
	 * @param string $parentId
	 * @return GuiCallResponse
	 * @throws UnknownGuiElementException
	 */
	function insertAsChildren(string $maskId, array $entryIds, string $parentId): GuiCallResponse;
}