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
use rocket\ui\gui\field\impl\relation\GuiEmbeddedEntry;
use n2n\util\type\attrs\InvalidAttributeException;
use rocket\ui\si\err\CorruptedSiDataException;

class EmbeddedEntriesInSiField extends InSiFieldAdapter {
	/**
	 * @var SiFrame
	 */
	private $frame;
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
	private ?string $summaryMaskId = null;
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

	private SiEmbeddedEntriesCollection $collection;
	
	/**
	 * @param string $typeCateogry
	 * @param Url $apiUrl
	 * @param SiEmbeddedEntryFactory $embeddedEntryFactory
	 * @param SiEmbeddedEntry[] $values
	 */
	function __construct(SiFrame $frame, SiEmbeddedEntryFactory $embeddedEntryFactory, private string $bulkyMaskId, array $values = []) {
		$this->frame = $frame;
		$this->collection = new SiEmbeddedEntriesCollection($embeddedEntryFactory);
		$this->setValue($values);

	}

	/**
	 * @param SiEmbeddedEntry[] $values
	 * @return EmbeddedEntriesInSiField
	 */
	function setValue(array $values): static {
		$this->collection->setEmbeddedEntries($values);
		return $this;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getValue(): array {
		return $this->collection->getEmbeddedEntries();
	}

	function setSummaryMaskId(?string $summaryMaskId): static {
		$this->summaryMaskId = $summaryMaskId;
		return $this;
	}

	function getSummaryMaskId(): ?string {
		return $this->summaryMaskId;
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
	
//	/**
//	 * @return boolean
//	 */
//	public function isReduced() {
//		return $this->reduced;
//	}
//
//	/**
//	 * @param boolean $reduced
//	 * @return EmbeddedEntriesInSiField
//	 */
//	public function setReduced(bool $reduced) {
//		$this->reduced = $reduced;
//		return $this;
//	}
	
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
			'values' => array_map(fn (SiEmbeddedEntry $v) => $v->toJsonStruct($n2nContext), $this->values),
			'frame' => $this->frame,
			'min' => $this->min,
			'max' => $this->max,
			'reduced' => $this->summaryMaskId !== null,
			'bulkyMaskId' => $this->bulkyMaskId,
			'summaryMaskId' => $this->summaryMaskId,
			'nonNewRemovable' => $this->nonNewRemovable,
			'sortable' => $this->sortable,
			'allowedSiTypeIds' => $this->allowedSiTypeIds,
			...parent::toJsonStruct($n2nContext)
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data, \n2n\core\container\N2nContext $n2nContext): bool {
		try {
			$valueBoundaryInputDatas = (new DataSet($data))->reqArray('valueBoundaryInputs', 'array');
			$valueBoundaryInputs = array_map(fn (array $d) => SiValueBoundaryInput::parse($d), $valueBoundaryInputDatas);
		} catch (InvalidAttributeException $e) {
			throw new CorruptedSiDataException(previous: $e->getMessage());
		}

		return $this->collection->handleInput($valueBoundaryInputs, $n2nContext);
	}
}
