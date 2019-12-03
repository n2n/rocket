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
namespace rocket\si\content\impl\split;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use rocket\si\content\impl\InSiFieldAdapter;
use n2n\util\uri\Url;
use rocket\si\content\SiEntry;

class SplitContextInSiField extends InSiFieldAdapter {
	/**
	 * @var string[]
	 */
	private $options;
	/**
	 * @var int
	 */
	private $min;
	/**
	 * @var string[]
	 */
	private $activeKeys = [];
	/**
	 * @var string[]
	 */
	private $associatedFieldIds = [];
	
	/**
	 * 
	 */
	function __construct(array $options) {
		ArgUtils::valArray($options, 'string');
		$this->options = $options;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int $min
	 * @return \rocket\si\content\impl\split\SplitContextInSiField
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-control-in';
	}
	
	/**
	 * @return string[]
	 */
	function getActiveKeys() {
		return $this->activeKeys;
	}
	
	/**
	 * @param array $activeKeys
	 * @return \rocket\si\content\impl\split\SplitContextInSiField
	 */
	function setActiveKeys(array $activeKeys) {
		$this->activeKeys = $activeKeys;
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param SiEntry $entry
	 * @return \rocket\si\content\impl\split\SplitContextInSiField
	 */
	function putEntry(string $key, string $label, SiEntry $entry) {
		ArgUtils::assertTrue(isset($this->options[$key]), 'Unknown key: ' . $key);
		$this->splitContents[$key] = SiSplitContent::createEntry($label, $entry);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param Url $apiUrl
	 * @param string $entryId
	 * @param string $fieldId
	 * @param bool $bulky
	 * @return \rocket\si\content\impl\split\SplitContextInSiField
	 */
	function putLazy(string $key, string $label, Url $apiUrl, string $entryId, bool $bulky) {
		$this->splitContents[$key] = SiSplitContent::createLazy($label, $apiUrl, $entryId, $bulky);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @return \rocket\si\content\impl\split\SplitContextInSiField
	 */
	function putUnavailable(string $key, string $label) {
		$this->splitContents[$key] = SiSplitContent::createUnavaialble($label);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'mandatory' => $this->mandatory,
			'associatedFieldIds' => $this->associatedFieldIds
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->value = (new DataSet($data))->reqInt('value', true);
	}
}
