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

class EnumInSiField extends InSiFieldAdapter {
	/**
	 * @var int|null
	 */
	private $value;
	/**
	 * @var string[]
	 */
	private $options = [];
	/**
	 * @var bool
	 */
	private $mandatory = false;
	
	/**
	 * @param int $value
	 */
	function __construct(array $options, ?string $value) {
		$this->setOptions($options);
		$this->value = $value;
	}
	
	/**
	 * @param string[] $options
	 * @return \rocket\si\content\impl\EnumInSiField
	 */
	function setOptions(array $options) {
		$this->options = $options;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getOptions() {
		return $this->options;
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\EnumInSiField
	 */
	function setValue(?string $value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\EnumInSiField
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
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'enum-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'options' => $this->options,
			'mandatory' => $this->mandatory
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->value = (new DataSet($data))->reqString('value', true);
	}
}
