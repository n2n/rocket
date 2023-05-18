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
namespace rocket\op\ei\manage\gui;

use n2n\util\ex\IllegalStateException;
use rocket\si\input\SiEntryInput;
use rocket\op\ei\EiType;
use n2n\util\type\ArgUtils;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\SiEntryIdentifier;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\si\content\SiValueBoundary;
use rocket\si\meta\SiStyle;

class EiGuiValueBoundary {
	

	/**
	 * @var string|null
	 */
	private ?string $selectedEiMaskId = null;
	/**	 
	 * @var EiGuiEntry[]
	 */
	private array $eiGuiEntries = [];

	function __construct(private readonly EiGuiDeclaration $eiGuiDeclaration, private readonly ?int $treeLevel = null) {
	}

	function getEiGuiDeclaration(): EiGuiDeclaration {
		return $this->eiGuiDeclaration;
	}
	
	/**
	 * @return int|null
	 */
	function getTreeLevel(): ?int {
		return $this->treeLevel;
	}


	/**
	 * @return EiEntry[] 
	 */
	function getEiEntries(): array {
		return array_map(function ($arg) { return $arg->getEiEntry(); }, $this->eiGuiEntries);
	}

	/**
	 * @param EiGuiEntry $eiGuiEntry
	 */
	function putEiGuiEntry(EiGuiEntry $eiGuiEntry): void {
		$eiType = $eiGuiEntry->getEiMask()->getEiType();
		
		ArgUtils::assertTrue($eiType->isA($this->eiGuiDeclaration->getContextEiMask()->getEiType()));
				
		$this->eiGuiEntries[$eiType->getId()] = $eiGuiEntry;
	}
	
	/**
	 * @return EiGuiEntry[]
	 */
	function getEiGuiEntries(): array {
		return $this->eiGuiEntries;
	}
	
//	/**
//	 * @param EiType $eiType
//	 * @return \rocket\op\ei\manage\gui\EiGuiEntry
//	 *@throws \OutOfBoundsException
//	 */
//	function getTypeDefByEiType(EiType $eiType) {
//		$eiTypeId = $eiType->getId();
//		if (isset($this->eiGuiEntries[$eiTypeId])) {
//			return $this->eiGuiEntries[$eiTypeId];
//		}
//
//		throw new \OutOfBoundsException('No EiGuiEntry for passed EiType available: ' . $eiType);
//	}


	/**
	 * @return SiValueBoundary
	 */
	function createSiValueBoundary(): SiValueBoundary {
		$viewMode = $this->eiGuiDeclaration->getViewMode();

		$siValueBoundary = new SiValueBoundary(/*$eiGuiValueBoundary->createSiEntryIdentifier(),*/
				new SiStyle(ViewMode::isBulky($viewMode), ViewMode::isReadOnly($viewMode)));
		$siValueBoundary->setTreeLevel($this->getTreeLevel());

		foreach ($this->eiGuiEntries as $key => $eiGuiEntry) {
			$siValueBoundary->putEntry($eiGuiEntry->getEiMask()->getEiTypePath(), $eiGuiEntry->createSiEntry());
		}

		if ($this->isEiGuiEntrySelected()) {
			$siValueBoundary->setSelectedMaskId($this->getSelectedEiGuiEntry()->getEiMask()->getEiType()->getId());
		}

		return $siValueBoundary;
	}

	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput): void {
		$eiMaskId = $siEntryInput->getMaskId();
		
		if (!isset($this->eiGuiEntries[$eiMaskId])) {
			throw new CorruptedSiInputDataException('EiMask not available: ' . $eiMaskId);
		}
		
		$this->selectedEiMaskId = $eiMaskId;
		$this->eiGuiEntries[$eiMaskId]->handleSiEntryInput($siEntryInput);
	}
	
	/**
	 * @return boolean
	 */
	function isEiGuiEntrySelected(): bool {
		return $this->selectedEiMaskId !== null;
	}
	
	/**
	 * @param string $eiMaskId
	 * @return EiGuiValueBoundary
	 * @throws \InvalidArgumentException
	 */
	function selectEiGuiEntryByEiMaskId(string $eiMaskId): static {
		if (isset($this->eiGuiEntries[$eiMaskId])) {
			$this->selectedEiMaskId = $eiMaskId;
			return $this;
		}
		
		throw new \InvalidArgumentException('Unknown EiType id: ' . $eiMaskId);
	}
	
	function unselectEiGuiEntry(): void {
		$this->selectedEiMaskId = null;
	}
	
	/**
	 * @return EiGuiEntry
	 * @throws IllegalStateException
	 */
	function getSelectedEiGuiEntry(): EiGuiEntry {
		if (!isset($this->eiGuiEntries[$this->selectedEiMaskId])) {
			throw new IllegalStateException('No selection.');
		}
		
		return $this->eiGuiEntries[$this->selectedEiMaskId];
	}
	
//	/**
//	 * @return SiEntryIdentifier
//	 */
//	function createSiEntryIdentifier() {
//		$typeId = $this->contextEiMask->getEiType()->getSupremeEiType()->getId();
//		$id = null;
//		if ($this->isTypeDefSelected()) {
//			$eiEntry = $this->getSelectedEiGuiEntry()->getEiEntry();
//			$id = $eiEntry->getPid();
//		}
//
//		return new SiEntryIdentifier($typeId, $id);
//	}
	
	/**
	 * @return EiEntry
	 */
	function getSelectedEiEntry(): EiEntry {
		return $this->getSelectedEiGuiEntry()->getEiEntry();
	}
	
	function save(): void {
		$this->getSelectedEiGuiEntry()->save();
	}

	function __toString() {
		return 'EiGuiValueBoundary';
	}
}