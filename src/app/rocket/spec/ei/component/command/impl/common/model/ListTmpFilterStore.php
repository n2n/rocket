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
namespace rocket\spec\ei\component\command\impl\common\model;

use rocket\spec\ei\manage\critmod\filter\data\FilterData;
use n2n\context\SessionScoped;

class ListTmpFilterStore implements SessionScoped {
	private $filterIds;
	private $filterDatas = array();
	private $sortDirections = array();
	private $searchStrs;
	
	private function _onSerialize() {}
	private function _onUnserialize() {}
	
	public function setFilterId($eiSpecId, $filterId) {
		$this->filterIds[$eiSpecId] = $filterId;
	}
	
	public function getFilterId($eiSpecId) {
		if (isset($this->filterIds[$eiSpecId])) {
			return $this->filterIds[$eiSpecId];
		}
		
		return null;
	}
	
	public function setTmpFilterData($eiSpecId, FilterData $filterData = null) {
		$this->filterDatas[$eiSpecId] = $filterData;
	}	
	
	public function getTmpFilterData($eiSpecId) {
		if (isset($this->filterDatas[$eiSpecId])) {
			return $this->filterDatas[$eiSpecId];
		}
		
		return null;
	}
	
	public function setTmpSortDirections($eiSpecId, array $sortDirections = null) {
		$this->sortDirections[$eiSpecId] = $sortDirections;
	}	
	
	public function getTmpSortDirections($eiSpecId) {
		if (isset($this->sortDirections[$eiSpecId])) {
			return $this->sortDirections[$eiSpecId];
		}
		
		return null;
	}
	
	public function setTmpSearchStr($eiSpecId, $searchStr) {
		$this->searchStrs[$eiSpecId] = $searchStr;
	}
	
	public function getTmpSearchStr($eiSpecId) {
		if (isset($this->searchStrs[$eiSpecId])) {
			return $this->searchStrs[$eiSpecId];
		}
		return null;
	}
}
