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
	 * @var SiEntry[]
	 */
	private $values;
	/**
	 * @var SiEntry[]
	 */
	private $summarySiEntries;
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
	 * @var string
	 */
	private $nonNewRemovable = true;
	
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
	 * @param Url|null $apiUrl
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
	 */
	function setApiUrl(?Url $apiUrl) {
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
	 * @param SiEntry[] $values
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
	 */
	function setValues(array $values) {
		ArgUtils::valArray($values, SiEntry::class);
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiEntry[]
	 */
	function getValues() {
		return $this->values;
	}
	
	/**
	 * @param SiEntry[] $summarySiEntries
	 * @return \rocket\si\content\impl\EmbeddedEntryInSiField
	 */
	function setSummarySiEntries(array $summarySiEntries) {
		ArgUtils::valArray($summarySiEntries, SiEntry::class);
		$this->summarySiEntries = $summarySiEntries;
		return $this;
	}
	
	/**
	 * @return \rocket\si\content\SiEntry[]
	 */
	function getSummarySiEntries() {
		return $this->summarySiEntries;
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
			'summaryEntries' => $this->summarySiEntries,
			'apiUrl' => (string) $this->apiUrl,
			'min' => $this->min,
			'max' => $this->max,
			'reduced' => $this->reduced,
			'nonNewRemovable' => $this->nonNewRemovable
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
		ArgUtils::valArrayReturn($values, SiEntry::class, $this->inputHandler, 'handleInput');
		$this->values = $values;
	}
}
