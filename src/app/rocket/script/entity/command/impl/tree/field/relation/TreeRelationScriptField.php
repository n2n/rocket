<?php
namespace rocket\script\entity\command\impl\tree\field\relation;

use rocket\script\entity\field\impl\relation\RelationScriptField;

interface TreeRelationScriptField extends RelationScriptField {

	public function getTargetTreeRootIdScriptField();
	
	public function getTargetTreeLeftScriptField();
	
	public function getTargetTreeRightScriptField();
}