<?php
namespace rocket\script\entity\command\impl\common\model;

use rocket\script\entity\filter\data\FilterData;
use n2n\model\SessionScoped;

class ListTmpFilterStore implements SessionScoped {
	private $filterIds;
	private $filterDatas = array();
	private $sortDirections = array();
	private $searchStrs;
	
	private function _onSerialize() {}
	private function _onUnserialize() {}
	
	public function setFilterId($entityScriptId, $filterId) {
		$this->filterIds[$entityScriptId] = $filterId;
	}
	
	public function getFilterId($entityScriptId) {
		if (isset($this->filterIds[$entityScriptId])) {
			return $this->filterIds[$entityScriptId];
		}
		
		return null;
	}
	
	public function setTmpFilterData($entityScriptId, FilterData $filterData = null) {
		$this->filterDatas[$entityScriptId] = $filterData;
	}	
	
	public function getTmpFilterData($entityScriptId) {
		if (isset($this->filterDatas[$entityScriptId])) {
			return $this->filterDatas[$entityScriptId];
		}
		
		return null;
	}
	
	public function setTmpSortDirections($entityScriptId, array $sortDirections = null) {
		$this->sortDirections[$entityScriptId] = $sortDirections;
	}	
	
	public function getTmpSortDirections($entityScriptId) {
		if (isset($this->sortDirections[$entityScriptId])) {
			return $this->sortDirections[$entityScriptId];
		}
		
		return null;
	}
	
	public function setTmpSearchStr($entityScriptId, $searchStr) {
		$this->searchStrs[$entityScriptId] = $searchStr;
	}
	
	public function getTmpSearchStr($entityScriptId) {
		if (isset($this->searchStrs[$entityScriptId])) {
			return $this->searchStrs[$entityScriptId];
		}
		return null;
	}
}