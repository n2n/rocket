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
namespace rocket\ui\si\content\impl\relation;

use n2n\util\type\ArgUtils;
use n2n\core\container\N2nContext;
use rocket\ui\si\err\CorruptedSiDataException;

class SiPanel {
//	use SiFieldErrorTrait;

	/**
	 * @var int
	 */
	private $min = 0;
	/**
	 * @var int|null
	 */
	private $max = null;
	/**
	 * @var bool
	 */
	private $reduced = true;
	/**
	 * @var bool
	 */
	private $sortable = false;
	/**
	 * @var string[]|null
	 */
	private $allowedTypeIds = null;
	/**
	 * @var bool
	 */
	private $nonNewRemovable = true;
	/**
	 * @var SiGridPos|null
	 */
	private $gridPos = null;

	private SiEmbeddedEntriesCollection $collection;

	function __construct(private readonly string $name, private string $label, private string $bulkyMaskId,
			private ?string $summaryMaskId, SiEmbeddedEntryFactory $embeddedEntryFactory) {
		$this->collection = new SiEmbeddedEntriesCollection($embeddedEntryFactory);
	}
	
	/**
	 * @return string
	 */
	function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	function getLabel(): string {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 * @return SiPanel
	 */
	function setLabel(string $label): static {
		$this->label = $label;
		return $this;
	}
		
	/**
	 * @return int
	 */
	function getMin(): int {
		return $this->min;
	}
	
	/**
	 * @param int $min
	 * @return SiPanel
	 */
	function setMin(int $min): static {
		$this->min = $min;
		return $this;
	}

	/**
	 * @return int|null
	 */
	function getMax(): ?int {
		return $this->max;
	}
	
	/**
	 * @param int|null $max
	 * @return SiPanel
	 */
	function setMax(?int $max): static {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	function isReduced(): bool {
		return $this->reduced;
	}
	
	/**
	 * @param boolean $reduced
	 * @return SiPanel
	 */
	function setReduced(bool $reduced): static {
		$this->reduced = $reduced;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isNonNewRemovable(): bool {
		return $this->nonNewRemovable;
	}
	
	/**
	 * @param bool $nonNewRemovable
	 * @return SiPanel
	 */
	function setNonNewRemovable(bool $nonNewRemovable): static {
		$this->nonNewRemovable = $nonNewRemovable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isSortable(): bool {
		return $this->sortable;
	}
	

	function setSortable(bool $sortable): static {
		$this->sortable = $sortable;
		return $this;
	}
	
	/**
	 * @return string[]|null
	 */
	function getAllowedTypeIds() {
		return $this->allowedTypeIds;
	}
	
	/**
	 * @param string[]|null $allowedTypeIds
	 * @return SiPanel
	 */
	function setAllowedTypeIds(?array $allowedTypeIds) {
		ArgUtils::valArray($allowedTypeIds, 'string', true);
		$this->allowedTypeIds = $allowedTypeIds === null ? null : array_values($allowedTypeIds);
		return $this;
	}
	
	/**
	 * @return \rocket\ui\si\content\impl\relation\SiGridPos|null
	 */
	function getGridPos() {
		return $this->gridPos;
	}
	
	/**
	 * @param \rocket\ui\si\content\impl\relation\SiGridPos|null $gridPos
	 * @return SiPanel
	 */
	function setGridPos(?SiGridPos $gridPos) {
		$this->gridPos = $gridPos;
		return $this;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getEmbeddedEntries(): array {
		return $this->collection->getEmbeddedEntries();
	}
	
	/**
	 * @param SiEmbeddedEntry[] $embeddedEntries
	 * @return SiPanel
	 */
	function setEmbeddedEntries(array $embeddedEntries): static {
		ArgUtils::valArray($embeddedEntries, SiEmbeddedEntry::class);
		$this->collection->setEmbeddedEntries($embeddedEntries);
		return $this;
	}

	function addEmbeddedEntry(SiEmbeddedEntry $embeddedEntry): static {
		$this->collection->addEmbeddedEntry($embeddedEntry);
		return $this;
	}

	/**
	 * @throws CorruptedSiDataException
	 */
	function handleInput(array $valueBoundaryInputs, N2nContext $n2nContext): bool {
		return $this->collection->handleInput($valueBoundaryInputs, $n2nContext);
	}
	
//	/**
//	 * @param SiEmbeddedEntry $embeddedEntry
//	 * @return SiPanel
//	 */
//	function addEmbeddedEntry(SiEmbeddedEntry $embeddedEntry) {
//		$this->values[] = $embeddedEntry;
//		return $this;
//	}
//
//	function getValue(): array {
//		return $this->values;
//	}

	function toJsonStruct(N2nContext $n2nContext): array {
		return [
			'name' => $this->name,
			'label' => $this->label,
			'bulkyMaskId' => $this->bulkyMaskId,
			'summaryMaskId' => $this->summaryMaskId,
			'min' => $this->min,
			'max' => $this->max,
			'reduced' => $this->reduced,
			'nonNewRemovable' => $this->nonNewRemovable,
			'sortable' => $this->sortable,
			'allowedTypeIds' => $this->allowedTypeIds,
			'gridPos' => $this->gridPos,
			'values' => array_map(fn (SiEmbeddedEntry $e) => $e->toJsonStruct($n2nContext),
					$this->collection->getEmbeddedEntries())
		];
	}
}
