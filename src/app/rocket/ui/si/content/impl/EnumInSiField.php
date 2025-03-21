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
namespace rocket\ui\si\content\impl;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraints;

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
	 * @var string[][]
	 */
	private $associatedPropIdsMap = [];
	/**
	 * @param string|null
	 */
	private $emptyLabel = null;
	
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
	function setOptions(array $options): static {
		ArgUtils::valArray($options, 'string');
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
	 * @return EnumInSiField
	 */
	function setValue(?string $value): static {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getValue(): ?string {
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
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'enum-in';
	}

	/**
	 * @param string[][] $associatedPropIdsMap
	 * @return EnumInSiField
	 */
	function setAssociatedPropIdsMap(array $associatedPropIdsMap): static {
		ArgUtils::valArray($associatedPropIdsMap, TypeConstraints::array(false, 'string'));
		$this->associatedPropIdsMap = $associatedPropIdsMap;
		return $this;
	}
	
	/**
	 * @return string[][]
	 */
	function getAssociatedPropIdsMap() {
		return $this->associatedPropIdsMap;
	}
	
	/**
	 * @param string|null $emptyLabel
	 * @return \rocket\si\content\impl\EnumInSiField
	 */
	function setEmptyLabel(?string $emptyLabel) {
		$this->emptyLabel = $emptyLabel;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getEmptyLabel() {
		return $this->emptyLabel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'value' => $this->value,
			'options' => $this->options,
			'mandatory' => $this->mandatory,
			'associatedPropIdsMap' => $this->associatedPropIdsMap,
			'emptyLabel' => $this->emptyLabel,
			...parent::toJsonStruct($n2nContext)
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data, \n2n\core\container\N2nContext $n2nContext): bool {
		$this->value = (new DataSet($data))->reqEnum('value',  array_keys($this->options), true);
		return true;
	}
}
