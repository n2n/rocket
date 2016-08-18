<?php
namespace rocket\script\entity\modificator;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\ScriptElement;
use rocket\script\entity\manage\display\DisplayDefinition;
use rocket\script\entity\manage\mapping\MappingDefinition;
use rocket\script\entity\manage\ScriptState;

interface ScriptModificator extends ScriptElement {
	
	public function setupScriptState(ScriptState $scriptState);
	
	public function setupMappingDefinition(MappingDefinition $mappingDefinition);
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping);
	
	public function setupDisplayDefinition(DisplayDefinition $displayDefinition);
}