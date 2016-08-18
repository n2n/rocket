<?php
namespace rocket\script\entity\manage\model;

use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\command\impl\common\model\ListEntryModel;

class EntryInfo implements EntryModel {
	private $displayDefinition;
	private $scriptState;
	private $scriptSelectionMapping;
	private $listEntryModel;
	
	public function __construct(DisplayDefinition $displayDefinition, ScriptState $scriptState, 
			ScriptSelectionMapping $scriptSelectionMapping) {
		$this->displayDefinition = $displayDefinition;
		$this->scriptState = $scriptState;
		$this->scriptSelectionMapping = $scriptSelectionMapping;
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
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\model\EntryModel::getScriptSelectionMapping()
	 */
	public function getScriptSelectionMapping() {
		return $this->scriptSelectionMapping;
	}
	
	public function hasListEntryModel() {
		return $this->listEntryModel !== null;
	} 
	
	public function getListEntryModel() {
		return $this->listEntryModel;
	}
	
	public function setListEntryModel(ListEntryModel $listEntryModel)  {
		$this->listEntryModel = $listEntryModel;
	}

	public function getOptionForm() {
		return $this->optionForm;
	}
}