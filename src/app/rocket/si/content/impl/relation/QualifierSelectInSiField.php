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
namespace rocket\si\content\impl\relation;

use n2n\util\type\attrs\DataSet;
use rocket\si\content\SiEntryQualifier;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use rocket\si\content\impl\InSiFieldAdapter;

class QualifierSelectInSiField extends InSiFieldAdapter {
	/**
	 * @var string
	 */
	private $typeCategory;
	/**
	 * @var SiEntryQualifier[]
	 */
	private $values;
	/**
	 * @var Url
	 */
	private $apiUrl;
	/**
	 * @var int
	 */
	private $min = 0;
	
	/**
	 * @var int|null
	 */
	private $max = null;
	
	/**
	 * @var SiEntryQualifier[]
	 */
	private $pickables = null;
	
	/**
	 * @param Url $apiUrl
	 * @param SiEntryQualifier[] $values
	 */
	function __construct(string $typeCategory, Url $apiUrl, array $values = []) {
		$this->typeCategory = $typeCategory;
		$this->setValues($values);	
		$this->apiUrl = $apiUrl;
	}
	
	/**
	 * @param SiEntryQualifier[] $values
	 * @return QualifierSelectInSiField
	 */
	function setValues(array $values) {
		foreach ($values as $value) {
			ArgUtils::assertTrue($value instanceof SiEntryQualifier && $value->getTypeCategory() === $this->typeCategory);
		}
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiEntryQualifier[]
	 */
	function getValues() {
		return $this->values;
	}
	
	/**
	 * @param Url|null $apiUrl
	 * @return QualifierSelectInSiField
	 */
	function setApiUrl(?Url $apiUrl) {
		$this->apiUrl = $apiUrl;
		return $this;
	}
	
	/**
	 * @return Url|null
	 */
	function getApiUrl() {
		return $this->apiUrl;
	}
	
	/**
	 * @param int $min
	 * @return QualifierSelectInSiField
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int|null $max
	 * @return QualifierSelectInSiField
	 */
	function setMax(?int $max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @param SiEntryQualifier[] $pickables
	 * @return QualifierSelectInSiField
	 */
	function setPickables(?array $pickables) {
		foreach ($pickables as $pickable) {
			ArgUtils::assertTrue($pickable instanceof SiEntryQualifier 
					&& $pickable->getTypeCategory() === $this->typeCategory);
		}
		$this->pickables = $pickables;
		return $this;
	}
	
	/**
	 * @return SiEntryQualifier[]
	 */
	function getPickables() {
		return $this->pickables;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'qualifier-select-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'typeCategory' => $this->typeCategory,
			'values' => $this->values,
			'apiUrl' => (string) $this->apiUrl,
			'min' => $this->min,
			'max' => $this->max,
			'pickables' => $this->pickables
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$siQualifiers = [];
		foreach ((new DataSet($data))->reqArray('values', 'array') as $data) {
			$siQualifiers[] = SiEntryQualifier::parse($data);
		}
		
		$this->values = $siQualifiers;
	}
}
