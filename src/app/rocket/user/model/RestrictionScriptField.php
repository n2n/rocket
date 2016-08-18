<?php

namespace rocket\user\model;

use n2n\core\N2nContext;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\field\PropertyScriptField;

interface RestrictionScriptField extends PropertyScriptField {
	
	public function createRestrictionSelectorItem(N2nContext $n2nContext, ScriptState $scriptState = null);	
}