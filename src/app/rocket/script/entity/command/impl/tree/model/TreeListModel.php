<?php
namespace rocket\script\entity\command\impl\tree\model;

use n2n\persistence\orm\NestedSetUtils;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\command\impl\common\model\ListEntryModel;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\model\EntryTreeListModel;

class TreeListModel implements EntryTreeListModel {
	private $scriptState;
	private $displayDefinition;
	private $entryModels = array();
	private $entryLevels = array();
	
	public function __construct(ScriptState $scriptState) {
		$this->scriptState = $scriptState;
		$this->displayDefinition = $scriptState->getScriptMask()->createDisplayDefinition($scriptState);
	}
				
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function getDisplayDefinition() {
		return $this->displayDefinition;
	}
	
	public function getEntryModels() {
		return $this->entryModels;
	}
	
	public function getEntryLevels() {
		return $this->entryLevels;
	}
	
	public function initialize() {
		$em = $this->scriptState->getEntityManager();
		$entityScript = $this->scriptState->getContextEntityScript();
		
		$nestedSetUtils = new NestedSetUtils($em, $entityScript->getEntityModel()->getClass());
		$criteria = $this->scriptState->createCriteria($em, NestedSetUtils::NODE_ALIAS);
		
		$mappingDefinition = $entityScript->createMappingDefinition();

		foreach ($nestedSetUtils->fetchNestedSetItems(null, false, $criteria) as $nestedSetItem) {
			$entity = $nestedSetItem->getObject();
			$id = $entityScript->extractId($entity);
			$scriptSelection = new ScriptSelection($id, $entity);
			$this->entryModels[$id] = new ListEntryModel($this->displayDefinition, $this->scriptState,
					$entityScript->createScriptSelectionMapping($mappingDefinition, $this->scriptState,
							$scriptSelection), $this);
			$this->entryLevels[$id] = $nestedSetItem->getLevel();
		}
	}
	
	

}