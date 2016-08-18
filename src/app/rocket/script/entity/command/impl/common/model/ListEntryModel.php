<?php

namespace rocket\script\entity\command\impl\common\model;

use rocket\script\entity\manage\model\EntryModel;
use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\model\EntryListModel;

class ListEntryModel implements EntryModel {
	private $displayDefinition;
	private $scriptState;
	private $scriptSelectionMapping;
	private $listEntryModel;
	/**
	 * @param DisplayDefinition $displayDefinition
	 * @param ScriptSelectionMapping $scriptSelectionMapping
	 */
	public function __construct(DisplayDefinition $displayDefinition, ScriptState $scriptState, 
			ScriptSelectionMapping $scriptSelectionMapping, EntryListModel $listEntryModel) {
		$this->displayDefinition = $displayDefinition;
		$this->scriptState = $scriptState;
		$this->scriptSelectionMapping = $scriptSelectionMapping;
		$this->listEntryModel = $listEntryModel;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\EntryModel::getScriptSelectionMapping()
	 */
	public function getScriptSelectionMapping() {
		return $this->scriptSelectionMapping;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\ManageModel::getDisplayDefinition()
	 */
	public function getDisplayDefinition() {
		return $this->displayDefinition;	
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\ManageModel::getScriptState()
	 */
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function hasListEntryModel() {
		return true;
	}
	
	public function getListEntryModel() {
		return $this->listEntryModel;
	}
}