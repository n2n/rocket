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

use n2n\util\ex\IllegalStateException;
use rocket\ui\si\input\SiEntryInput;
use n2n\util\type\ArgUtils;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\meta\SiStyle;
use n2n\l10n\N2nLocale;

class GuiValueBoundary {
	

	/**
	 * @var string|null
	 */
	private ?string $selectedEiMaskId = null;
	/**	 
	 * @var GuiEntry[]
	 */
	private array $guiEntries = [];

	function __construct(private readonly ?int $treeLevel = null) {
	}

	
	/**
	 * @return int|null
	 */
	function getTreeLevel(): ?int {
		return $this->treeLevel;
	}

	/**
	 * @param GuiEntry $guiEntry
	 */
	function putGuiEntry(GuiEntry $guiEntry): void {
		$this->guiEntries[$guiEntry->getMaskId()] = $guiEntry;
	}
	
	/**
	 * @return GuiEntry[]
	 */
	function getGuiEntries(): array {
		return $this->guiEntries;
	}

	function createSiValueBoundary(N2nLocale $n2nLocale): SiValueBoundary {
		$siValueBoundary = new SiValueBoundary();
		$siValueBoundary->setTreeLevel($this->getTreeLevel());

		foreach ($this->guiEntries as $key => $guiEntry) {
			$siValueBoundary->putEntry($guiEntry->getMaskId(), $guiEntry->createSiEntry($n2nLocale));
		}

		if ($this->isEiGuiEntrySelected()) {
			$siValueBoundary->setSelectedMaskId($this->getSelectedMaskId());
		}

		return $siValueBoundary;
	}

	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput): bool {
		$eiMaskId = $siEntryInput->getMaskId();
		
		if (!isset($this->guiEntries[$eiMaskId])) {
			throw new CorruptedSiInputDataException('EiMask not available: ' . $eiMaskId);
		}
		
		$this->selectedEiMaskId = $eiMaskId;
		return $this->guiEntries[$eiMaskId]->handleSiEntryInput($siEntryInput);
	}
	
	/**
	 * @return boolean
	 */
	function isEiGuiEntrySelected(): bool {
		return $this->selectedEiMaskId !== null;
	}
	
	/**
	 * @param string $eiMaskId
	 * @return GuiValueBoundary
	 * @throws \InvalidArgumentException
	 */
	function selectGuiEntryByMaskId(string $eiMaskId): static {
		if (isset($this->guiEntries[$eiMaskId])) {
			$this->selectedEiMaskId = $eiMaskId;
			return $this;
		}
		
		throw new \InvalidArgumentException('Unknown EiType id: ' . $eiMaskId);
	}
	
	function unselectGuiEntry(): void {
		$this->selectedEiMaskId = null;
	}
	
	/**
	 * @return GuiEntry
	 * @throws IllegalStateException
	 */
	function getSelectedGuiEntry(): GuiEntry {
		return $this->guiEntries[$this->getSelectedMaskId()];
	}

	function getSelectedMaskId(): string {
		if (!isset($this->guiEntries[$this->selectedEiMaskId])) {
			throw new IllegalStateException('No selection.');
		}

		return $this->selectedEiMaskId;
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
		return $this->getSelectedGuiEntry()->getEiEntry();
	}
	
	function save(): void {
		$this->getSelectedGuiEntry()->save();
	}

	function __toString() {
		return 'EiGuiValueBoundary';
	}
}