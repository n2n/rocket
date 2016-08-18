<?php
namespace rocket\script\entity\field\impl\relation;

use rocket\script\entity\field\EntityPropertyScriptField;

interface RelationScriptField extends EntityPropertyScriptField {
	public function getFieldRelation();
}