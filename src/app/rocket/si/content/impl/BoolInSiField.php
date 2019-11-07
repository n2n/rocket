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
use n2n\util\type\ArgUtils;

class BoolInSiField extends InSiFieldAdapter {
	/**
	 * @var bool
	 */
	private $value;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	/**
	 * @var string[]
	 */
	private $onAssociatedFieldIds = [];
	/**
	 * @var string[]
	 */
	private $offAssociatedFieldIds = [];
	
	/**
	 * @param int $value
	 */
	function __construct(bool $value) {
		$this->value = $value;	
	}
	
	/**
	 * @param int|null $value
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setValue(?int $value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMandatory() {
		return $this->mandatory;
	}
	
	function getOnAssociatedFieldIds() {
		return $this->onAssociatedFieldIds;
	}

	/**
	 * @param string[] $onAssociatedFieldIds
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setOnAssociatedFieldIds(array $onAssociatedFieldIds) {
		ArgUtils::valArray($onAssociatedFieldIds, 'string');
		$this->onAssociatedFieldIds = $onAssociatedFieldIds;
		return $this;
	}

	/**
	 * @return string[]
	 */
	function getOffAssociatedFieldIds() {
		return $this->offAssociatedFieldIds;
	}

	/**
	 * @param string[] $offAssociatedFieldIds
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setOffAssociatedFieldIds(array $offAssociatedFieldIds) {
		ArgUtils::valArray($offAssociatedFieldIds, 'string');
		$this->offAssociatedFieldIds = $offAssociatedFieldIds;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'boolean-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'mandatory' => $this->mandatory,
			'onAssociatedFieldIds' => $this->onAssociatedFieldIds,
			'offAssociatedFieldIds' => $this->offAssociatedFieldIds
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
