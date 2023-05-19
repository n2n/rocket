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
use rocket\op\ei\manage\gui\EiValueBoundary;
use rocket\op\ei\manage\DefPropPath;

class EiuValueBoundaryTypeDef {

	function __construct(private readonly EiValueBoundary $eiValueBoundary, private ?EiuGuiDeclaration $eiuGuiDeclaration,
			private readonly EiuAnalyst $eiuAnalyst) {

	}

	function getEiValueBoundary(): EiValueBoundary {
		return $this->eiValueBoundary;
	}

	/**
	 * @param SiEntryInput $siEntryInput
	 * @return EiuValueBoundaryTypeDef
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$this->getEiGuiValueBoundaryMulti()->handleSiEntryInput($siEntryInput);
		return $this;
	}

	/**
	 * @return \rocket\op\ei\util\gui\EiuValueBoundary[]
	 */
	function entryGuis() {
		$eiuValueBoundarys = [];
		foreach ($this->getEiGuiValueBoundaryMulti()->getEiGuiValueBoundaries() as $eiTypeId => $eiGuiValueBoundary) {
			$eiuValueBoundarys[$eiTypeId] = new EiuGuiEntry($eiGuiValueBoundary, null, null, $this->eiuAnalyst);
		}
		return $eiuValueBoundarys;
	}

	/**
	 * @return \rocket\op\ei\util\gui\EiuValueBoundary
	 */
	function selectedEntryGui() {
		return new EiuValueBoundary($this->eiGuiValueBoundaryMultiResult->getSelectedEiGuiValueBoundary(), null, null, $this->eiuAnalyst);
	}

	/**
	 * @param bool $siControlsIncluded
	 * @throws EiuPerimeterException
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function createBulkyEntrySiGui(bool $siControlsIncluded) {
		if (!ViewMode::isBulky($this->getEiGuiValueBoundaryMulti()->getViewMode())) {
			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not bulky.');
		}

		return new BulkyEntrySiGui($this->eiGuiValueBoundaryMultiResult->createSiDeclaration(),
				$this->eiGuiValueBoundaryMultiResult->createSiEntry($siControlsIncluded));
	}

	/**
	 * @param bool $siControlsIncluded
	 * @throws EiuPerimeterException
	 * @return \rocket\si\content\impl\basic\CompactEntrySiGui
	 */
	function createCompactEntrySiGui(bool $siControlsIncluded) {
		if (!ViewMode::isCompact($this->getEiGuiValueBoundaryMulti()->getViewMode())) {
			throw new EiuPerimeterException('EiGuiValueBoundaryMulti is not compact.');
		}

		return new CompactEntrySiGui($this->eiGuiValueBoundaryMultiResult->createSiDeclaration(),
				$this->eiGuiValueBoundaryMultiResult->createSiEntry($siControlsIncluded));
	}

	/**
	 * @param DefPropPath|string $defPropPath
	 * @return EiuGuiField
	 */
	function field($defPropPath) {
		return new EiuGuiField(DefPropPath::create($defPropPath), $this, $this->eiuAnalyst);
	}
}
