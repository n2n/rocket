<?php
namespace rocket\script\entity\modificator\impl;

use rocket\script\entity\modificator\ScriptModificator;
use rocket\script\entity\IndependentScriptElementAdapter;
use rocket\script\entity\modificator\IndependentScriptModificator;
use rocket\script\entity\manage\mapping\MappingDefinition;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\manage\ScriptState;

abstract class IndependentScriptModificatorAdapter extends IndependentScriptElementAdapter implements IndependentScriptModificator {
	public function getTypeName() {
		return self::shortenTypeName(parent::getTypeName(), array('Script', 'Constraint'));
	}
	
	public function equals($obj) {
		return $obj instanceof ScriptModificator && parent::equals($obj);
	}

	public function setupScriptState(ScriptState $scriptState) {}
	
	public function setupMappingDefinition(MappingDefinition $mappingDefinition) {}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {}
	
	public function setupDisplayDefinition(DisplayDefinition $displayDefinition) {}
}