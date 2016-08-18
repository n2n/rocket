<?php
namespace rocket\script\entity\filter;

use n2n\util\StringUtils;
use n2n\persistence\orm\EntityAdapter;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityAnnotations;
use rocket\script\entity\filter\data\FilterData;

class Filter extends EntityAdapter {
	private static function _annotations(AnnotationSet $as) {
		$as->annotateClass(EntityAnnotations::TABLE, array('name' => 'rocket_filter'));
		$as->annotateClass(EntityAnnotations::PROPERTIES, array('names' => array('id', 'entityScriptId', 'name', 
				'filterDataJson', 'sortDirectionsJson')));
	}
	
	private $id;
	private $entityScriptId;
	private $name;
	private $filterDataJson = '[]';
	private $sortDirectionsJson  = '[]';
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getEntityScriptId() {
		return $this->entityScriptId;
	}
	
	public function setEntityScriptId($entityScriptId) {
		$this->entityScriptId = $entityScriptId;
	}

	public function readFilterData() {
		$data = array();
		if (!empty($this->filterDataJson)) {
			$data = StringUtils::jsonDecode($this->filterDataJson, true);
		}
		return FilterData::createFromArray($data);
	}
	
	public function writeFilterData(FilterData $filterData) {
		$this->filterDataJson = StringUtils::jsonEncode($filterData->toArray());		
	}
	
	public function getSortDirections() {
		if (empty($this->filterDataJson)) {
			return array();
		}
		return StringUtils::jsonDecode($this->sortDirectionsJson, true);
	}
	
	public function setSortDirections(array $sortDirections) {
		$this->sortDirectionsJson = StringUtils::jsonEncode($sortDirections);
	}
}