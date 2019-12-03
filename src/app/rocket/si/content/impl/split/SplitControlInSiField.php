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

class SplitControlInSiField extends InSiFieldAdapter {
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
	 * @return \rocket\si\content\impl\split\SplitControlInSiField
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
	 * @return \rocket\si\content\impl\split\SplitControlInSiField
	 */
	function setActiveKeys(array $activeKeys) {
		$this->activeKeys = $activeKeys;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getAssociatedFieldIds() {
		return $this->associatedFieldIds;
	}

	/**
	 * @param string[] $onAssociatedFieldIds
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setAssociatedFieldIds(array $associatedFieldIds) {
		ArgUtils::valArray($associatedFieldIds, 'string');
		$this->associatedFieldIds = $associatedFieldIds;
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
