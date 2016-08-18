<?php
namespace rocket\script\entity\command\impl\common\model;

use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\Dispatchable;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\model\EntryListModel;

class ListModel implements Dispatchable, EntryListModel {
	private static function _annotations(AnnotationSet $as) {
		$as->m('executePartialCommand', DispatchAnnotations::MANAGED_METHOD);
	} 
	
	private $scriptState;
	private $listSize;
	private $displayDefinition;
	private $entryModels = array();
	
	private $currentPageNo;
	private $numPages;
	private $numObjects;
		
	public function __construct(ScriptState $scriptState, $listSize) {
		$this->scriptState = $scriptState;
		$this->displayDefinition = $scriptState->getScriptMask()->createDisplayDefinition($scriptState);
		$this->listSize = $listSize;
	}
	
	public function getDisplayDefinition() {
		return $this->displayDefinition;
	}
	
	public function getEntryModels() {
		return $this->entryModels;
	}
	
	public function initialize($pageNo) {
		if (!is_numeric($pageNo) || $pageNo < 1) return false;
		
		$em = $this->scriptState->getEntityManager();
		
		$countCriteria = $this->scriptState->createCriteria($em, 'o', false);
		$countCriteria->select('COUNT(o)');
		$this->numObjects = $countCriteria->fetchSingle();
		
		$this->currentPageNo = $pageNo;
		$limit = ($pageNo - 1) * $this->listSize;
		if ($limit > $this->numObjects) {
			return false;
		}
		$this->numPages = ceil($this->numObjects / $this->listSize);
		if (!$this->numPages) $this->numPages = 1;
		
		$criteria = $this->scriptState->createCriteria($em, 'o', false);
		$criteria->limit($limit, $this->listSize);
		$entityScript = $this->scriptState->getContextEntityScript();
		$mappingDefinition = $entityScript->createMappingDefinition();
		
		foreach ($criteria->fetchArray() as $entity) {
			$id = $entityScript->extractId($entity);
			$scriptSelection = new ScriptSelection($id, $entity);
			$this->entryModels[$id] = new ListEntryModel($this->displayDefinition, $this->scriptState,
					$entityScript->createScriptSelectionMapping($mappingDefinition, $this->scriptState, 
							$scriptSelection), $this);
		}
		
		return true;
	}
	
	public function getNumPages() {
		return $this->numPages;
	}
	
	public function getCurrentPageNo() {
		return $this->currentPageNo;
	}
	
	public function getNumObjects() {
		return $this->numObjects;
	}
	
	public function getScriptState() {
		return $this->scriptState;
	}
	
	protected $selectedObjectIds = array();
	protected $executedPartialCommandKey = null;
	
	public function getSelectedObjectIds() {
		return $this->selectedObjectIds;
	}
	
	public function setSelectedObjectIds(array $selectedObjectIds) {
		$this->selectedObjectIds = $selectedObjectIds;
	}
	
	public function getExecutedPartialCommandKey() {
		return $this->executedPartialCommandKey;
	}
	
	public function setExecutedPartialCommandKey($executedPartialCommandKey) {
		$this->executedPartialCommandKey = $executedPartialCommandKey;
	}
	
	private function _validation() {}
	
	public function executePartialCommand() {
		$executedScriptCommand = null;
		if (isset($this->partialScriptCommands[$this->executedPartialCommandKey])) {
			$executedScriptCommand = $this->partialScriptCommands[$this->executedPartialCommandKey];
		}
		
		$selectedObjects = array();
		foreach ($this->selectedObjectIds as $entryId) {
			if (!isset($this->scriptSelections[$entryId])) continue;
			
			$selectedObjects[$entryId] = $this->scriptSelections[$entryId];
		}
		
		if (!sizeof($selectedObjects)) return;
		
		$executedScriptCommand->processEntries($this->scriptState, $selectedObjects);
	}
}