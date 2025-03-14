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
use n2n\core\container\N2nContext;

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
	private $onAssociatedPropIds = [];
	/**
	 * @var string[]
	 */
	private $offAssociatedPropIds = [];
	
	/**
	 * @param int $value
	 */
	function __construct(bool $value) {
		$this->value = $value;	
	}

	/**
	 * @param bool|null $value
	 * @return BoolInSiField
	 */
	function setValue(bool $value): static {
		$this->value = $value;
		return $this;
	}

	function getValue(): bool {
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
	
	function getOnAssociatedPropIds() {
		return $this->onAssociatedPropIds;
	}

	/**
	 * @param string[] $onAssociatedPropIds
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setOnAssociatedPropIds(array $onAssociatedPropIds) {
		ArgUtils::valArray($onAssociatedPropIds, 'string');
		$this->onAssociatedPropIds = $onAssociatedPropIds;
		return $this;
	}

	/**
	 * @return string[]
	 */
	function getOffAssociatedPropIds() {
		return $this->offAssociatedPropIds;
	}

	/**
	 * @param string[] $offAssociatedPropIds
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	function setOffAssociatedPropIds(array $offAssociatedPropIds) {
		ArgUtils::valArray($offAssociatedPropIds, 'string');
		$this->offAssociatedPropIds = $offAssociatedPropIds;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'boolean-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'value' => $this->value,
			'mandatory' => $this->mandatory,
			'onAssociatedPropIds' => $this->onAssociatedPropIds,
			'offAssociatedPropIds' => $this->offAssociatedPropIds,
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
		$this->value = (new DataSet($data))->reqBool('value', true);
		return true;
	}
}
