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
namespace rocket\ui\si\content;

use rocket\ui\si\input\SiEntryInput;
use rocket\ui\si\input\CorruptedSiInputDataException;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiStyle;
use n2n\core\container\N2nContext;

class SiValueBoundary implements \JsonSerializable {

	private $selectedMaskId = null;
	private $entries = [];
	private $treeLevel;
	private $style;

	/**
	 * @param SiEntryIdentifier $identifier
	 * @param SiStyle $style
	 */
	function __construct() {
	}

	/**
	 * @return int|null
	 */
	function getTreeLevel(): ?int {
		return $this->treeLevel;
	}

	function setTreeLevel(?int $treeLevel): static {
		$this->treeLevel = $treeLevel;
		return $this;
	}

	/**
	 * @return SiEntry[]
	 */
	function getEntries(): array {
		return $this->entries;
	}


	function putEntry(SiEntry $entry): static {
		$this->entries[$entry->getQualifier()->getIdentifier()->getMaskIdentifier()->getId()] = $entry;
		
		return $this;
	}
	
	/**
	 * @return SiEntry
	 */
	function getSelectedEntry(): SiEntry {
		return $this->entries[$this->getSelectedMaskId()];
	}
	
	/**
	 * @param string $maskId
	 * @return SiValueBoundary
	 */
	function setSelectedMaskId(?string $maskId): static {
		ArgUtils::valEnum($maskId, array_keys($this->entries), nullAllowed: true);
		$this->selectedMaskId = $maskId;
		return $this;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	function getSelectedMaskId(): ?string {
		IllegalStateException::assertTrue($this->selectedMaskId !== null);
		
		return $this->selectedMaskId;
	}
	
	function jsonSerialize(): mixed {
		$entries = array();
		foreach ($this->entries as $id => $buildup) {
			$entries[$id] = $buildup;
		}
				
		return [
//			'identifier' => $this->identifier,
			'treeLevel' => $this->treeLevel,
			'style' => $this->style,
			'entries' => $entries,
			'selectedMaskId' => $this->selectedMaskId
		];
	}

	/**
	 * @param SiEntryInput $entryInput
	 * @throws CorruptedSiInputDataException
	 */
	function handleEntryInput(SiEntryInput $entryInput, N2nContext $n2nContext): bool {
		$typeId = $entryInput->getIdentifier()->getMaskIdentifier()->getId();
		
		try {
			$this->setSelectedMaskId($typeId);
		} catch (\InvalidArgumentException $e) {
			throw new CorruptedSiInputDataException('Invalid type id: ' . $typeId, 0, $e);
		}
		
		$this->getSelectedEntry()->handleEntryInput($entryInput, $n2nContext);
		return true;
	}
	
}
