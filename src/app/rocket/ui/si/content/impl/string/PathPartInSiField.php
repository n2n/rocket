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
namespace rocket\ui\si\content\impl\string;

use n2n\util\type\attrs\DataSet;
use rocket\ui\si\content\impl\meta\AddonsSiFieldTrait;
use n2n\core\container\N2nContext;
use n2n\util\type\attrs\InvalidAttributeException;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\ui\si\content\impl\InSiFieldAdapter;

class PathPartInSiField extends InSiFieldAdapter {
	use MinMaxLengthTrait, AddonsSiFieldTrait;
	
	/**
	 * @var string|null
	 */
	private $value;
	/**
	 * @var bool
	 */
	private $mandatory = false;

	private ?string $basedOnPropName = null;

	function __construct(?string $value) {
		$this->value = $value;	
	}

	/**
	 * @param string|null $value
	 * @return PathPartInSiField
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
	 * @return \rocket\ui\si\content\impl\string\PathPartInSiField
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
		return 'path-part-in';
	}

	function getBasedOnPropName(): ?string {
		return $this->basedOnPropName;
	}

	function setBasedOnPropName(?string $basedOnPropName): static {
		$this->basedOnPropName = $basedOnPropName;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(N2nContext $n2nContext): array {
		return [
			'value' => $this->value,
			'minlength' => $this->minlength,
			'maxlength' => $this->maxlength,
			'mandatory' => $this->mandatory,
			'prefixAddons' => $this->prefixAddons,
			'suffixAddons' => $this->suffixAddons,
			'basedOnPropName' => $this->basedOnPropName,
			...parent::toJsonStruct($n2nContext)
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @param array $data
	 * @param N2nContext $n2nContext
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data, N2nContext $n2nContext): bool {
		try {
			$this->value = (new DataSet($data))->reqString('value', true);
		} catch (InvalidAttributeException $e) {
			throw new CorruptedSiDataException(previous: $e);
		}
		return true;
	}
}
