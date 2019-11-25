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
use rocket\si\content\impl\InSiFieldAdapter;
use rocket\si\content\SiField;
use n2n\util\uri\Url;

class SplitInSiField extends InSiFieldAdapter {
	
	/**
	 * @var SiSplitContent[]
	 */
	private $splitContents = [];
	
	/**
	 * @var \Closure|null
	 */
	private $saveCallback = null;
	
	/**
	 * @param int $value
	 */
	function __construct() {
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-in';
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param SiField $field
	 * @return \rocket\si\content\impl\split\SplitInSiField
	 */
	function putField(string $key, string $label, SiField $field) {
		$this->splitContents[$key] = SiSplitContent::createField($label, $field);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param Url $apiUrl
	 * @param string $entryId
	 * @param string $fieldId
	 * @param bool $bulky
	 * @return \rocket\si\content\impl\split\SplitInSiField
	 */
	function putLazy(string $key, string $label, Url $apiUrl, string $entryId, string $fieldId, bool $bulky,
			\Closure $handleInputCallback) {
		$this->splitContents[$key] = SiSplitContent::createLazy($label, $apiUrl, $entryId, $fieldId, $bulky,
				$handleInputCallback);
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @return \rocket\si\content\impl\split\SplitInSiField
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
			'splitContentsMap' => $this->splitContents
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data, array $uploadDefinitions) {
		$dataMap = (new DataSet($data))->reqArray('value', 'array');
		
		foreach ($this->splitContents as $key => $splitContent) {
			if (!$splitContent->isReadOnly() && isset($dataMap[$key])) {
				$splitContent->handleInput($dataMap[$key], $uploadDefinitions);
			}
		}
	}
}