<?php
namespace rocket\script\entity\modificator\impl;

use rocket\script\entity\modificator\ScriptModificator;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\ScriptElementAdapter;
use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\manage\mapping\MappingDefinition;
use rocket\script\entity\manage\ScriptState;

abstract class ScriptModificatorAdapter extends ScriptElementAdapter implements ScriptModificator {
	
	public function setupScriptState(ScriptState $scriptState) {}
	
	public function setupMappingDefinition(MappingDefinition $mappingDefinition) {}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping) {}
	
	public function setupDisplayDefinition(DisplayDefinition $displayDefinition) {}
}