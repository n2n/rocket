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

use rocket\ui\si\api\request\SiEntryInput;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiStyle;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\request\SiValueBoundaryInput;

class SiValueBoundary {

	private $selectedTypeId = null;
	/**
	 * @var SiEntry[]
	 */
	private array $entries = [];
	private $treeLevel;

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
		$this->entries[$entry->getQualifier()->getIdentifier()->getMaskIdentifier()->getTypeId()] = $entry;
		
		return $this;
	}

	function containsEntryWith(string $maskId, ?string $entryId): bool {
		foreach ($this->entries as $entry) {
			$identifier = $entry->getQualifier()->getIdentifier();
			if ($identifier->getMaskIdentifier()->getId() === $maskId
					&& $identifier->getId() === $entryId) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * @return SiEntry
	 */
	function getSelectedEntry(): SiEntry {
		return $this->entries[$this->getSelectedTypeId()];
	}
	
	/**
	 * @param string $maskId
	 * @return SiValueBoundary
	 */
	function setSelectedTypeId(?string $maskId): static {
		ArgUtils::valEnum($maskId, array_keys($this->entries), nullAllowed: true);
		$this->selectedTypeId = $maskId;
		return $this;
	}

	/**
	 * @return string[]
	 */
	function getMaskIds(): array {
		return array_map(fn (SiEntry $e) => $e->getQualifier()->getIdentifier()->getMaskIdentifier()->getId(),
				$this->entries);
	}
	
	/**
	 * @throws IllegalStateException
	 */
	function getSelectedTypeId(): ?string {
		IllegalStateException::assertTrue($this->selectedTypeId !== null);
		
		return $this->selectedTypeId;
	}

	function __toString() {
		return 'SiValueBoundary TODO: insert identifier...';
	}
	
	function toJsonStruct(N2nContext $n2nContext): array {
		$entries = array();
		foreach ($this->entries as $id => $buildup) {
			$entries[$id] = $buildup;
		}
				
		return [
//			'identifier' => $this->identifier,
			'treeLevel' => $this->treeLevel,
			'entries' => array_map(fn (SiEntry $e) => $e->toJsonStruct($n2nContext), $entries),
			'selectedTypeId' => $this->selectedTypeId
		];
	}

	/**
	 * @param SiValueBoundaryInput $input
	 * @param N2nContext $n2nContext
	 * @return bool
	 * @throws CorruptedSiDataException
	 */
	function handleInput(SiValueBoundaryInput $input, N2nContext $n2nContext): bool {
		$typeId = $input->getSelectedTypeId();
		
		try {
			$this->setSelectedTypeId($typeId);
		} catch (\InvalidArgumentException $e) {
			throw new CorruptedSiDataException('Invalid type id: ' . $typeId, 0, $e);
		}
		
		return $this->getSelectedEntry()->handleEntryInput($input->getEntryInput(), $n2nContext);
	}
	
}
