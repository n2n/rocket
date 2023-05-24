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
namespace rocket\op\ei\util\gui;

use rocket\op\ei\util\EiuAnalyst;
use rocket\si\input\SiEntryInput;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\op\ei\util\EiuPerimeterException;
use rocket\si\content\impl\basic\CompactEntrySiGui;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGuiValueBoundary;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\si\input\CorruptedSiInputDataException;

class EiuGuiValueBoundary {

	function __construct(private readonly EiGuiValueBoundary $eiGuiValueBoundary, private ?EiuGuiDeclaration $eiuGuiDeclaration,
			private readonly EiuAnalyst $eiuAnalyst) {

	}

	function getEiGuiValueBoundary(): EiGuiValueBoundary {
		return $this->eiGuiValueBoundary;
	}

	function guiDeclaration(): EiuGuiDeclaration {
		return $this->eiuGuiDeclaration ?? $this->eiuGuiDeclaration
				= new EiuGuiDeclaration($this->eiGuiValueBoundary->getEiGuiDeclaration(), $this->eiuAnalyst);
	}

	/**
	 * @param SiEntryInput $siEntryInput
	 * @return EiuGuiValueBoundary
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput): static {
		$this->eiGuiValueBoundary->handleSiEntryInput($siEntryInput);
		return $this;
	}

	/**
	 * @return EiuGuiEntry[]
	 */
	function guiEntries(): array {
		$eiuGuiEntries = [];
		foreach ($this->eiGuiValueBoundary->getEiGuiEntries() as $eiMaskId => $eiGuiEntry) {
			$eiuGuiEntries[$eiMaskId] = new EiuGuiEntry($eiGuiEntry, null, $this->eiuAnalyst);
		}
		return $eiuGuiEntries;
	}

	/**
	 * @return EiuGuiEntry
	 */
	function selectedGuiEntry(): EiuGuiEntry {
		return new EiuGuiEntry($this->eiGuiValueBoundary->getSelectedEiGuiEntry(), null, $this->eiuAnalyst);
	}

	/**
	 * @return boolean
	 */
	function isGuiEntrySelected(): bool {
		return $this->eiGuiValueBoundary->isEiGuiEntrySelected();
	}

	function copy(bool $bulky = null, bool $readOnly = null, array $defPropPathsArg = null,
			bool $entryGuiControlsIncluded = null): EiuGuiValueBoundary {
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);

		$eiGuiDeclaration = $this->eiGuiValueBoundary->getEiGuiDeclaration();
		$newViewMode = ViewMode::determine(
				$bulky ?? ViewMode::isBulky($eiGuiDeclaration->getViewMode()),
				$readOnly ?? ViewMode::isReadOnly($eiGuiDeclaration->getViewMode()),
				ViewMode::isAdd($eiGuiDeclaration->getViewMode()));

		$eiFrameUtil = new EiFrameUtil($this->eiuAnalyst->getEiFrame(true));

		$newEiGuiValueBoundary = $eiFrameUtil->copyEiGuiValueBoundary($this->eiGuiValueBoundary, $newViewMode,
				$defPropPaths, $entryGuiControlsIncluded);

		return new EiuGuiValueBoundary($newEiGuiValueBoundary, null, $this->eiuAnalyst);
	}

	/**
	 * @return BulkyEntrySiGui
	 */
	function createBulkyEntrySiGui(): BulkyEntrySiGui {
		if (!ViewMode::isBulky($this->eiGuiValueBoundary->getEiGuiDeclaration()->getViewMode())) {
			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not bulky.');
		}

		$n2nLocale = $this->eiuAnalyst->getN2nContext(true)->getN2nLocale();
		$siFrame = $this->eiuAnalyst->getEiFrame(true)->createSiFrame();
		$siDeclaration = $this->eiGuiValueBoundary->getEiGuiDeclaration()->createSiDeclaration($n2nLocale);
		$siValueBoundary = $this->eiGuiValueBoundary->createSiValueBoundary($n2nLocale);

		return new BulkyEntrySiGui($siFrame, $siDeclaration, $siValueBoundary);
	}

	/**
	 * @param bool $siControlsIncluded
	 * @throws EiuPerimeterException
	 * @return CompactEntrySiGui
	 */
	function createCompactEntrySiGui(bool $siControlsIncluded): CompactEntrySiGui {
		if (!ViewMode::isCompact($this->getEiGuiValueBoundary()->getEiGuiDeclaration()->getViewMode())) {
			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not compact.');
		}

		$n2nLocale = $this->eiuAnalyst->getN2nContext(true)->getN2nLocale();
		$siFrame = $this->eiuAnalyst->getEiFrame(true)->createSiFrame();
		$siDeclaration = $this->eiGuiValueBoundary->getEiGuiDeclaration()->createSiDeclaration($n2nLocale);
		$siValueBoundary = $this->eiGuiValueBoundary->createSiValueBoundary($n2nLocale);

		return new CompactEntrySiGui($siFrame, $siDeclaration, $siValueBoundary);
	}


}
