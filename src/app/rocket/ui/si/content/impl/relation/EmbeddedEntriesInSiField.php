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
use n2n\util\type\attrs\DataSet;
use n2n\util\uri\Url;
use rocket\ui\si\content\impl\InSiFieldAdapter;
use rocket\ui\si\api\request\SiEntryInput;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\api\request\SiValueBoundaryInput;

class EmbeddedEntriesInSiField extends InSiFieldAdapter {
	/**
	 * @var SiFrame
	 */
	private $frame;
	/**
	 * @var SiEmbeddedEntryFactory
	 */
	private SiEmbeddedEntryFactory $embeddedEntryFactory;
	/**
	 * @var SiEmbeddedEntry[]
	 */
	private $values;
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
	private $reduced = false;
	/**
	 * @var bool
	 */
	private $nonNewRemovable = true;
	/**
	 * @var bool
	 */
	private $sortable = false;
	/**
	 * @var string[]|null
	 */
	private $allowedSiTypeIds = null;
	
	/**
	 * @param string $typeCateogry
	 * @param Url $apiUrl
	 * @param SiEmbeddedEntryFactory $embeddedEntryFactory
	 * @param SiEmbeddedEntry[] $values
	 */
	function __construct(SiFrame $frame, SiEmbeddedEntryFactory $embeddedEntryFactory, array $values = []) {
		$this->frame = $frame;
		$this->embeddedEntryFactory = $embeddedEntryFactory;
		$this->setValue($values);
	}

	/**
	 * @param SiEmbeddedEntry[] $values
	 * @return EmbeddedEntriesInSiField
	 */
	function setValue(array $values): static {
		ArgUtils::valArray($values, SiEmbeddedEntry::class);
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getValue(): array {
		return $this->values;
	}
	
	/**
	 * @param int $min
	 * @return \rocket\si\content\impl\relation\EmbeddedEntriesInSiField
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int|null $max
	 * @return \rocket\si\content\impl\relation\EmbeddedEntriesInSiField
	 */
	function setMax(?int $max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @return boolean
	 */
	public function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param boolean $reduced
	 * @return EmbeddedEntriesInSiField
	 */
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isNonNewRemovable() {
		return $this->nonNewRemovable;
	}
	
	/**
	 * @param bool $nonNewRemovable
	 * @return EmbeddedEntriesInSiField
	 */
	public function setNonNewRemovable(bool $nonNewRemovable) {
		$this->nonNewRemovable = $nonNewRemovable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isSortable() {
		return $this->sortable;
	}
	
	/**
	 * @param bool $sortable
	 * @return EmbeddedEntriesInSiField
	 */
	public function setSortable(bool $sortable) {
		$this->sortable = $sortable;
		return $this;
	}
	
	/**
	 * @return string[]|null
	 */
	public function isAllowedTypeIds() {
		return $this->allowedTypeIds;
	}
	
	/**
	 * @param string[]|null $allowedTypeIds
	 * @return EmbeddedEntriesInSiField
	 */
	public function setAllowedTypeIds(?array $allowedTypeIds): static {
		ArgUtils::valArray($allowedTypeIds, 'string', true);
		$this->allowedTypeIds = $allowedTypeIds;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'embedded-entries-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'values' => $this->values,
			'frame' => $this->frame,
			'min' => $this->min,
			'max' => $this->max,
			'reduced' => $this->reduced,
			'nonNewRemovable' => $this->nonNewRemovable,
			'sortable' => $this->sortable,
			'allowedSiTypeIds' => $this->allowedSiTypeIds,
			...parent::toJsonStruct($n2nContext)
		];
	}

	private function findExisting(string $maskId, string $entryId): ?SiEmbeddedEntry {
		foreach ($this->values as $value) {
			if ($value->getContent()->getValueBoundary()->containsEntryWith($maskId, $entryId)) {
				return $value;
			}
		}

		return null;
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data, \n2n\core\container\N2nContext $n2nContext): bool {
		$values = [];
		foreach ((new DataSet($data))->reqArray('valueBoundaryInputs', 'array') as $entryInputData) {
			$valueBoundaryInput = SiValueBoundaryInput::parse($entryInputData);
			$entryInput = $valueBoundaryInput->getEntryInput();
			$maskId = $entryInput->getMaskId();
			$entryId = $entryInput->getEntryId();

			if ($entryId !== null) {
				$siEmbeddedEntry = $this->findExisting($maskId, $entryId);
			} else {
				$siEmbeddedEntry = $this->embeddedEntryFactory->createSiEmbeddedEntry($entryInput->getMaskId(), $entryInput->getEntryId());
			}

			if ($siEmbeddedEntry === null) {
				continue;
			}

			$siEmbeddedEntry->getContent()->getValueBoundary()->handleInput($valueBoundaryInput, $n2nContext);
			$values[] = $siEmbeddedEntry;
		}


		ArgUtils::valArrayReturn($values, $this->embeddedEntryFactory, 'handleInput', SiEmbeddedEntry::class);
		$this->values = $values;
		return true;
	}
}
