<?php
namespace rocket\script\entity\filter;

use rocket\script\entity\filter\Filter;
use n2n\model\RequestScoped;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\OrmUtils;
use rocket\script\entity\filter\data\FilterData;

class FilterStore implements RequestScoped {
	private $em;
	
	private function _init(EntityManager $em) {
		$this->em = $em;
	}
	
	public function containsFilterName($entityScriptId, $filterName) {
		return OrmUtils::createCountCriteria($this->em, Filter::getClass(), 
						array('entityScriptId' => $entityScriptId, 'name' => $filterName))
				->fetchSingle();
	}
	
	public function getFiltersByEntityScriptId($entityScriptId) {
		return $this->em
				->createSimpleCriteria(Filter::getClass(), array('entityScriptId' => $entityScriptId))
				->fetchArray();
	}
		
// 	public function getFilterNames(ScriptState $scriptState) {
// 		$scriptId = $scriptState->getContextEntityScript()->getId();
// 		if (isset($this->filterDatas[$scriptId])) {
// 			return array_keys($this->filterDatas[$scriptId]);
// 		}	
		
// 		return array();
// 	}

	public function createFilter($entityScriptId, $name, FilterData $filterData, array $orderDirections) {
		$filter = new Filter();
		$filter->setEntityScriptId($entityScriptId);
		$filter->setName($name);
		$filter->writeFilterData($filterData);
		$filter->setSortDirections($orderDirections);
		$this->em->persist($filter);
		$this->em->flush();
		return $filter;
	}

	public function mergeFilter(Filter $filter) {
		return $this->em->merge($filter);
	}
	
	public function removeFilter(Filter $filter) {
		$this->em->remove($filter);
	}
	
// 	public function removeFilterDataByFilterName(ScriptState $scriptState, $filterName) {
// 		$scriptId = $scriptState->getContextEntityScript()->getId();
		
// 		if (isset($this->filterDatas[$scriptId])) {
// 			unset($this->filterDatas[$scriptId][$filterName]);
// 			$this->persist();
// 		}
// 	}
	
// 	private function persist() {
// 		IoUtils::putContentsSafe($this->filtersFilePath,
// 				serialize($this->filterDatas));
// 	}
}