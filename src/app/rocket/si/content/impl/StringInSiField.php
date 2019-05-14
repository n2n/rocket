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

class StringInSiField extends InSiFieldAdapter {
	/**
	 * @var string|null
	 */
	private $value;
	/**
	 * @var int|null
	 */
	private $maxlength;
	/**
	 * @var bool
	 */
	private $multiline = false;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	
	function __construct(?string $value) {
		$this->value = $value;	
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setValue(?string $value) {
		$this->maxlength;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param int|null $maxlength
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxlength = $maxlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMaxlength() {
		return $this->maxlength;
	}
	
	/**
	 * @param bool $multiline
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setMultiline(bool $multiline) {
		$this->multiline = $multiline;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMultiline() {
		return $this->multiline;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\StringInSiField
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
		return 'string-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'maxlength' => $this->maxlength,
			'multiline' => $this->multiline,
			'mandatory' => $this->mandatory
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
	}
}