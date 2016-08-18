<?php

namespace rocket\script\entity\modificator\impl\date;

use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\mapping\OnWriteMappingListener;
use rocket\script\entity\field\PropertyScriptField;

class LastModScriptModificator extends ScriptModificatorAdapter {

	private $scriptField;
	
	public function __construct(PropertyScriptField $scriptField) {
		$this->scriptField = $scriptField;
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$that = $this;
		$scriptSelectionMapping->registerListener(new OnWriteMappingListener(function() 
				use ($scriptState, $scriptSelection, $that) {
			$this->scriptField->getPropertyAccessProxy()->setValue($scriptSelection->getCurrentEntity(), new \DateTime());
		}));
	}
}