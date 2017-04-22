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
namespace rocket\spec\ei\manage\critmod\impl\model;

use n2n\util\StringUtils;
use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use rocket\spec\ei\manage\critmod\filter\data\FilterData;
use n2n\persistence\orm\annotation\AnnoTable;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use n2n\util\JsonDecodeFailedException;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\sort\SortData;

class CritmodSave extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_critmod_save'));
	}
	
	private $id;
	private $eiTypeId;
	private $eiMaskId;
	private $name;
	private $filterDataJson = '[]';
	private $sortDataJson  = '[]';
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName(string $name) {
		$this->name = $name;
	}
	
	public function getEiTypeId() {
		return $this->eiTypeId;
	}
	
	public function setEiTypeId(string $eiTypeId) {
		$this->eiTypeId = $eiTypeId;
	}
	
	public function getEiMaskId() {
		return $this->eiTypeId;
	}
	
	public function setEiMaskId(string $eiMaskId = null) {
		$this->eiMaskId = $eiMaskId;
	}

	public function readFilterGroupData(): FilterGroupData {
		$data = array();
		try {
			$data = StringUtils::jsonDecode($this->filterDataJson, true);
		} catch (JsonDecodeFailedException $e) {}
		return FilterGroupData::create(new Attributes($data));
	}
	
	public function writeFilterData(FilterGroupData $filterGroupData) {
		$this->filterDataJson = StringUtils::jsonEncode($filterGroupData->toAttrs());		
	}
	
	public function readSortData(): SortData {
		$data = array();
		try {
			$data = StringUtils::jsonDecode($this->sortDataJson, true);
		} catch (JsonDecodeFailedException $e) {}
		return SortData::create(new Attributes($data));
	}
	
	public function writeSortData(SortData $sortData) {
		$this->sortDataJson = StringUtils::jsonEncode($sortData->toAttrs());
	}
}
