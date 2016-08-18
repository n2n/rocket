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
namespace rocket\spec\ei\manage\critmod\sort;

use n2n\util\config\Attributes;
use n2n\reflection\ArgUtils;
use n2n\persistence\orm\criteria\Criteria;
use n2n\util\col\GenericArrayObject;

class SortData {
	private $sortItemDatas;

	public function __construct() {
		$this->sortItemDatas = new GenericArrayObject(null, SortItemData::class);
	}
	
	/**
	 * @return SortItemData[]
	 */
	public function getSortItemDatas(): \ArrayObject {
		return $this->sortItemDatas;
	}
	
	public function toAttrs(): array {
		$attrs = array();
		
		foreach ($this->sortItemDatas as $sortItemData) {
			$attrs[$sortItemData->getSortFieldId()] = $sortItemData->getDirection();
		}
		
		return $attrs;
	}

	public static function create(Attributes $attributes): SortData {
		$sortData = new SortData();
		$sortItemDatas = $sortData->getSortItemDatas();
		foreach ($attributes->toArray() as $sortFieldId => $direction) {
			if (!is_string($direction)) continue;
			try {
				$sortItemDatas[] = new SortItemData($sortFieldId, $direction);
			} catch (\InvalidArgumentException $e) {}
		}
		
		return $sortData;
	}
}

class SortItemData {
	private $sortFieldId;
	private $direction;
	
	public function __construct(string $sortFieldId, string $direction) {
		$this->sortFieldId = $sortFieldId;
		$this->setDirection($direction);
	}
	
	public function getSortFieldId(): string {
		return $this->sortFieldId;
	}
	
	public function setSortFieldId(string $sortFieldId) {
		$this->sortFieldId = $sortFieldId;
	}
	
	public function getDirection(): string {
		return $this->direction;
	}
		
	public function setDirection(string $direction) {
		ArgUtils::valEnum($direction, Criteria::getOrderDirections());
		$this->direction = $direction;
	}
}
