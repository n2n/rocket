<?php
namespace rocket\script\entity\command\impl\common\model;

use rocket\script\entity\filter\FilterCriteriaConstraint;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\Dispatchable;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\FilterModelFactory;

class ListQuickSearchModel implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->m('search', DispatchAnnotations::MANAGED_METHOD);
		$as->m('clear', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $scriptState;
	private $tmpFilterStore;
	private $quickSearchableModel;
	
	protected $searchStr;
	
	public function __construct(ScriptState $scriptState, ListTmpFilterStore $tmpFilterStore) {
		$this->scriptState = $scriptState;
		$this->tmpFilterStore = $tmpFilterStore;
		$this->quickSearchableModel = FilterModelFactory::createQuickSearchableModel($scriptState);
		$this->searchStr = $tmpFilterStore->getTmpSearchStr($scriptState->getContextEntityScript()->getId());
	}
	
	public function isActive() {
		return $this->searchStr !== null;
	}
	
	public function applyToScriptState(ScriptState $scriptState) {
		$criteriaConstraint = $this->quickSearchableModel->createCriteriaConstraint($this->searchStr);
		if ($criteriaConstraint !== null) {
			$scriptState->addCriteriaConstraint($criteriaConstraint);
		}
	}
	
	public function getSearchStr() {
		return $this->searchStr;
	}
	
	public function setSearchStr($searchStr) {
		$this->searchStr = $searchStr;
	}
	
	private function _validation() { }
	
	public function search() {
		$this->tmpFilterStore->setTmpSearchStr($this->scriptState->getContextEntityScript()->getId(), $this->searchStr);
	}
	
	public function clear() {
		$this->tmpFilterStore->setTmpSearchStr($this->scriptState->getContextEntityScript()->getId(), null);
	}
	
}