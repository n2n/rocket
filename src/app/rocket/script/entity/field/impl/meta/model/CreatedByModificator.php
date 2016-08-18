<?php

namespace rocket\script\entity\field\impl\meta\model;

use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;

class CreatedByModificator extends ScriptModificatorAdapter {
	private $fieldId;
	
	public function __construct($fieldId) {
		$this->fieldId = $fieldId;
	}
		
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $mapping) {
		if (!$mapping->getScriptSelection()->isNew()) return;
		
		$mapping->setValue($this->fieldId, $scriptState->getManageState()->getUser()->getId());
	}
	
}