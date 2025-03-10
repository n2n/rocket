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
namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\ui\si\content\impl\relation\SiEmbeddedEntry;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\entry\EiEntry;
use n2n\util\type\CastUtils;
use rocket\op\ei\util\spec\EiuType;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\api\request\SiValueBoundaryInput;
use rocket\ui\gui\GuiValueBoundary;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\util\Eiu;

class EmbeddedGuiCollection {
	/**
	 * @param EiuFrame
	 */
	private $eiuFrame;
	/**
	 * @var bool
	 */
	private $readOnly;
	/**
	 * @var bool
	 */
	private $summaryRequired;
	/**
	 * @var int
	 */
	private $min;
	/** 
	 * @var EiuGuiValueBoundary[]
	 */
	private array $guiValueBoundaries = [];
	/**
	 * 
	 * @var EiuType[]
	 */
	private $allowedEiuTypes = [];

	/**
	 * @param bool $readOnly
	 * @param bool $summaryRequired
	 * @param int $min
	 * @param EiuFrame $eiuFrame
	 * @param array $allowedEiuTypes
	 */
	function __construct(bool $readOnly, bool $summaryRequired, int $min, ?EiuFrame $eiuFrame, ?array $allowedEiuTypes) {
		$this->readOnly = $readOnly;
		$this->summaryRequired = $summaryRequired;
		$this->min = $min;
		$this->eiuFrame = $eiuFrame;
		$this->allowedEiuTypes = $allowedEiuTypes;
	
	}

	/**
	 * 
	 */
	function clear(): void {
		$this->guiValueBoundaries = [];
	}


	function add(EiuEntry $eiuEntry): GuiValueBoundary {
		return $this->guiValueBoundaries[] = $eiuEntry->createGuiValueBoundary(ViewMode::determine(true, $this->readOnly, $eiuEntry->isNew()), $this->readOnly);
	}
	
	function fillUp(): void {
		$num = $this->min - count($this->guiValueBoundaries);
		
		if ($num <= 0) {
			return;
		}
		
		IllegalStateException::assertTrue($this->eiuFrame !== null);
		$eiuGuiDeclaration  = $this->eiuFrame->contextEngine()->newMultiGuiDeclaration(true, $this->readOnly,
				true, $this->allowedEiuTypes);
		for ($i = 0; $i < $num; $i++) {
			$eiuEntries = $this->eiuFrame->newPossibleEntries();
			$this->guiValueBoundaries[] = $this->eiuFrame->createGuiValueBoundary($eiuEntries,
					ViewMode::determine(true, $this->readOnly, true));
		}
	}
	
	function addNew(): GuiValueBoundary {
		IllegalStateException::assertTrue($this->eiuFrame !== null);

		$eiuEntries = $this->eiuFrame->newPossibleEntries();
		return $this->guiValueBoundaries[] = $this->eiuFrame->createGuiValueBoundary($eiuEntries,
				ViewMode::determine(true, $this->readOnly, true));
	}
	
	function sort(EiPropPath $orderEiPropPath): void {
		uasort($this->guiValueBoundaries, function(EiuGuiValueBoundary $a, $b) use ($orderEiPropPath) {
			$aValue = $a->selectedGuiEntry()->entry()->getScalarValue($orderEiPropPath);
			$bValue = $b->selectedGuiEntry()->entry()->getScalarValue($orderEiPropPath);
			
			if ($aValue == $bValue) {
				return 0;
			}
			
			return ($aValue < $bValue) ? -1 : 1;
		});
	}
	
	/**
	 * @return int
	 */
	function count() {
		return count($this->guiValueBoundaries);
	}
	
// 	/**
// 	 * @return NULL|string[]
// 	 */      
// 	function buildAllowedSiTypeIds() {
// 		if ($this->allowedEiuMasks === null) {
// 			return null;
// 		}
		
// 		$allowedSiTypeIds = [];
// 		foreach ($this->allowedEiuMasks as $eiuMask) {
// 			$allowedSiTypeIds[] = $eiuMask->type()->getSiTypeId();
// 		}
// 		return $allowedSiTypeIds;
// 	}

	/**
	 * @return array<SiEmbeddedEntry>
	 */
	function createSiEmbeddedEntries(): array {
		return array_values(array_map(
				function ($eiuGuiValueBoundary) { return $this->createSiEmbeddedEntry($eiuGuiValueBoundary); },
				$this->guiValueBoundaries));
	}

	private function createSiEmbeddedEntry(GuiValueBoundary $guiValueBoundary): SiEmbeddedEntry {
		$eiu = new Eiu($this->eiuFrame);
		return new SiEmbeddedEntry(
				$eiu->f()->gui()->createBulkyGui(),
				($this->summaryRequired ?
						$eiuGuiValueBoundary->copy(false, true, entryGuiControlsIncluded: false)->createCompactEntrySiGui(false):
						null));
	}
	
	/**
	 * @param string $id
	 * @return EiuGuiValueBoundary|null
	 */
	function find(string $id): ?EiuGuiValueBoundary {
		foreach ($this->guiValueBoundaries as $eiuGuiValueBoundary) {
			if ($eiuGuiValueBoundary->isGuiEntrySelected()
					&& $id === $eiuGuiValueBoundary->selectedGuiEntry()->entry()->getPid(false)) {
				return $eiuGuiValueBoundary;
			}
		}
		
		return null;
	}
	
	/**
	 * @param SiValueBoundaryInput[] $siValueBoundaryInputs
	 * @throws CorruptedSiDataException
	 */
	function handleSiEntryInputs(array $siValueBoundaryInputs): void {
		$newEiuGuiEntrys = [];
		foreach ($siValueBoundaryInputs as $siValueBoundaryInput) {
			CastUtils::assertTrue($siValueBoundaryInput instanceof SiValueBoundaryInput);
			
			$eiuGuiEntry = null;
			$id = $siValueBoundaryInput->getEntryInput()->getId();
			
			if ($id !== null && null !== ($eiuGuiEntry = $this->find($id))) {
				$eiuGuiEntry->handleSiEntryInput($siValueBoundaryInput);
				$newEiuGuiEntrys[] = $eiuGuiEntry;
				continue;
			}
			
			$newEiuGuiEntrys[] = $this->addNew()->handleSiEntryInput($siValueBoundaryInput);
		}
		
		$this->guiValueBoundaries = $newEiuGuiEntrys;
	}

	/**
	 * @param EiPropPath|null $orderEiPropPath
	 * @return EiEntry[]
	 */
	function save(?EiPropPath $orderEiPropPath): array {
		$values = [];
		$i = 0;
		foreach ($this->guiValueBoundaries as $eiuGuiValueBoundary) {
			if (!$eiuGuiValueBoundary->isGuiEntrySelected()) {
				continue;
			}
			
			$eiuGuiValueBoundary->selectedGuiEntry()->save();
			$values[] = $eiuEntry = $eiuGuiValueBoundary->selectedGuiEntry()->entry();
			
			if (null === $orderEiPropPath) {
				continue;
			}
			
			$i += 10;
			$eiuEntry->setScalarValue($orderEiPropPath, $i);
		}
		return $values;
	}
}