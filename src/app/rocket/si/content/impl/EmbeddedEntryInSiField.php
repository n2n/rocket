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
namespace rocket\si\content\impl;

use n2n\util\type\attrs\DataSet;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use rocket\si\content\SiEntry;
use rocket\si\input\SiEntryInput;
use rocket\si\content\SiEmbeddedEntry;
use rocket\si\content\SiType;

class EmbeddedEntryInSiField extends InSiFieldAdapter {
	
	/**
	 * @var Url
	 */
	private $apiUrl;
	/**
	 * @var EmbeddedEntryInputHandle
	 */
	private $inputHandler;
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
	 * @var string|null
	 */
	private $pastCategory = null;
	/**
	 * @var SiType[]|null
	 */
	private $allowedSiTypes = null;
	
	/**
	 * @param Url $apiUrl
	 * @param EmbeddedEntryInputHandle $inputHandler
	 * @param SiEntry[] $values
	 */
	function __construct(Url $apiUrl, EmbeddedEntryInputHandle $inputHandler, array $values = []) {
		$this->apiUrl = $apiUrl;
		$this->inputHandler = $inputHandler;
		$this->setValues($values);
	}
	
	/**
	 * @param Url $apiUrl
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
	 */
	function setApiUrl(Url $apiUrl) {
		$this->apiUrl = $apiUrl;
		return $this;
	}
	
	/**
	 * @return Url|null
	 */
	function getApiUrl() {
		return $this->apiUrl;
	}
	
	/**
	 * @param SiEmbeddedEntry[] $values
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
	 */
	function setValues(array $values) {
		ArgUtils::valArray($values, SiEmbeddedEntry::class);
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getValues() {
		return $this->values;
	}
	
	/**
	 * @param int $min
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
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
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
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
	 * @return EmbeddedEntryInSiField
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
	 * @return EmbeddedEntryInSiField
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
	 * @return EmbeddedEntryInSiField
	 */
	public function setSortable(bool $sortable) {
		$this->sortable = $sortable;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function isPasteCategory() {
		return $this->pasteCategory;
	}
	
	/**
	 * @param string $pasteCategory
	 * @return EmbeddedEntryInSiField
	 */
	public function setPasteCategory(string $pasteCategory) {
		$this->pasteCategory = $pasteCategory;
		return $this;
	}
	
	/**
	 * @return SiType[]|null
	 */
	public function isAllowedTypes() {
		return $this->allowedTypes;
	}
	
	/**
	 * @param SiType[]|null $allowedTypes
	 * @return EmbeddedEntryInSiField
	 */
	public function setAllowedTypes(?array $allowedTypes) {
		ArgUtils::valArray($allowedTypes, SiType::class, true);
		$this->allowedTypes = $allowedTypes;
		return $this;
	}
	
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'embedded-entry-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'values' => $this->values,
			'apiUrl' => (string) $this->apiUrl,
			'min' => $this->min,
			'max' => $this->max,
			'reduced' => $this->reduced,
			'nonNewRemovable' => $this->nonNewRemovable,
			'sortable' => $this->sortable,
			'pastCategory' => $this->pastCategory,
			'allowedSiTypes' => $this->allowedSiTypes
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$siEntryInputs = [];
		foreach ((new DataSet($data))->reqArray('entryInputs', 'array') as $entryInputData) {
			$siEntryInputs[] = SiEntryInput::parse($entryInputData);
		}
		$values = $this->inputHandler->handleInput($siEntryInputs);
		ArgUtils::valArrayReturn($values, $this->inputHandler, 'handleInput', SiEmbeddedEntry::class);
		$this->values = $values;
	}
}
