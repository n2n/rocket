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
namespace rocket\ui\gui;

use rocket\ui\si\api\SiApiModel;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\content\SiPartialContent;
use rocket\ui\si\content\SiField;
use rocket\ui\si\meta\SiMask;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\api\response\SiCallResponse;
use rocket\ui\gui\err\UnknownGuiElementException;
use rocket\ui\si\err\UnknownSiElementException;

class GuiSiApiModel implements SiApiModel {

	/**
	 * @var GuiMask[]
	 */
	private array $guiMasks = [];

	function __construct(private GuiApiModel $guiApiModel) {

	}

	function getSiMask(string $maskId): SiMask {
		if (isset($this->guiMasks[$maskId])) {
			return $this->guiMasks[$maskId]->getSiMask();
		}

		try {
			$this->guiMasks[$maskId] = $this->guiApiModel->lookupGuiMask($maskId);
		} catch (UnknownGuiElementException $e) {
			throw new UnknownSiElementException(previous: $e);
		}

		return $this->guiMasks[$maskId]->getSiMask();
	}

	function getSiMaskControl(string $maskId, string $controlName): SiControl {
		$siControl = $this->getSiMask($maskId)->getControl($controlName);
		if ($siControl === null) {
			throw new UnknownSiElementException('Mask ' . $maskId . ' does contain any control with name '
					. $controlName);
		}
		return $siControl;
	}

	function getSiEntryControl(string $maskId, string $entryId, string $controlName): SiControl {
		$siControl = $this->getSiMask($maskId)->getControl($controlName);
		if ($siControl === null) {
			throw new UnknownSiElementException('Mask ' . $maskId . ' does contain any control with name '
					. $controlName);
		}
		return $siControl;
	}

	function lookupSiField(string $maskId, string $entryId, string $fieldName): SiField {
		$entry = $this->lookupSiValueBoundary($maskId, $entryId, null)->getSelectedEntry();
		if ($entry->containsFieldName($fieldName)) {
			return $entry->getField($fieldName);
		}

		throw new UnknownSiElementException('Field "' . $fieldName . '" does not exist in entry: '
				. $maskId . '#' . $entryId);
	}

	function lookupSiValueBoundary(string $maskId, ?string $entryId, ?array $allowedFieldNames): SiValueBoundary {
		if ($entryId !== null) {
			try {
				return $this->guiApiModel->lookupGuiValueBoundary($maskId, $entryId)->getSiValueBoundary();
			} catch (UnknownGuiElementException $e) {
				throw new UnknownSiElementException('Could not create a new SiValueBoundary based on maskId: '
						. $maskId, previous: $e);
			}
		}

		try {
			return $this->guiApiModel->lookupGuiValueBoundary($maskId, $entryId)->getSiValueBoundary();
		} catch (UnknownGuiElementException $e) {
			throw new UnknownSiElementException('Could not lookup a SiValueBoundary for: '
					. $maskId . '#' . $entryId, previous: $e);
		}
	}

	/**
	 * @throws UnknownSiElementException
	 */
	function lookupSiPartialContent(string $maskId, int $from, int $num, ?string $quickSearchStr, ?array $allowedFieldNames): SiPartialContent {
		try {
			$guiValueBoundaries = $this->guiApiModel->lookupGuiValueBoundaries($maskId, $from, $num, $quickSearchStr);
			$num = $this->guiApiModel->countGuiValueBoundaries($maskId, $quickSearchStr);
		} catch (UnknownGuiElementException $e) {
			throw new UnknownSiElementException(previous: $e);
		}

		return new SiPartialContent($num, array_map(fn (GuiValueBoundary $gvb) => $gvb->getSiValueBoundary(),
				$guiValueBoundaries));
	}

	function copySiValueBoundary(SiValueBoundary $boundary, string $maskId): SiValueBoundary {
		// TODO: Implement copySiValueBoundary() method.
	}

	function insertSiEntriesAfter(string $maskId, string $entryIds, string $afterEntryId): SiCallResponse {
		// TODO: Implement insertSiEntriesAfter() method.
	}

	function insertSiEntriesBefore(string $maskId, string $entryIds, string $beforeEntryId): SiCallResponse {
		// TODO: Implement insertSiEntriesBefore() method.
	}

	function insertSiEntriesAsChildren(string $maskId, string $entryIds, string $parentId): SiCallResponse {
		// TODO: Implement insertSiEntriesAsChildren() method.
	}
}