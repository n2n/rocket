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
namespace rocket\ui\si\content\impl\relation;

use n2n\util\type\attrs\DataSet;
use rocket\ui\si\content\SiObjectQualifier;
use n2n\util\type\ArgUtils;
use rocket\ui\si\content\impl\InSiFieldAdapter;
use rocket\ui\si\meta\SiFrame;

class ObjectQualifiersSelectInSiField extends InSiFieldAdapter {
	/**
	 * @var SiObjectQualifier[]
	 */
	private array $values;
	/**
	 * @var int
	 */
	private int $min = 0;
	
	/**
	 * @var int|null
	 */
	private ?int $max = null;
	
	/**
	 * @var SiObjectQualifier[]
	 */
	private ?array $pickables = null;

	/**
	 * @param SiFrame $frame
	 * @param SiObjectQualifier[] $value
	 */
	function __construct(private SiFrame $frame, private string $maskId, array $value = []) {
		$this->setValue($value);
	}
	
	/**
	 * @param SiObjectQualifier[] $values
	 * @return ObjectQualifiersSelectInSiField
	 */
	function setValue(array $values): static {
		ArgUtils::valArray($values, SiObjectQualifier::class);
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiObjectQualifier[]
	 */
	function getValue(): array {
		return $this->values;
	}
	
	/**
	 * @param int $min
	 * @return ObjectQualifiersSelectInSiField
	 */
	function setMin(int $min): static {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMin(): int {
		return $this->min;
	}
	
	/**
	 * @param int|null $max
	 * @return ObjectQualifiersSelectInSiField
	 */
	function setMax(?int $max): static {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMax(): ?int {
		return $this->max;
	}
	
	/**
	 * @param SiObjectQualifier[] $pickables
	 * @return ObjectQualifiersSelectInSiField
	 */
	function setPickables(?array $pickables): static {
		ArgUtils::valArray($pickables, SiObjectQualifier::class, true);
		$this->pickables = $pickables;
		return $this;
	}
	
	/**
	 * @return SiObjectQualifier[]
	 */
	function getPickables(): ?array {
		return $this->pickables;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'object-qualifiers-select-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'frame' => $this->frame,
			'maskId' => $this->maskId,
			'values' => $this->values,
			'min' => $this->min,
			'max' => $this->max,
			'pickables' => $this->pickables,
			...parent::toJsonStruct($n2nContext)
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data, \n2n\core\container\N2nContext $n2nContext): bool {
		$siObjectQualifiers = [];
		foreach ((new DataSet($data))->reqArray('values', 'array') as $data) {
			$siObjectQualifiers[] = SiObjectQualifier::parse($data);
		}
		
		$this->values = $siObjectQualifiers;
		return true;
	}
}
