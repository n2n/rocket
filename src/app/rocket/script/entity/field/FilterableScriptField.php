<?php
namespace rocket\script\entity\field;

use rocket\script\entity\manage\ScriptState;
use n2n\core\N2nContext;

interface FilterableScriptField extends EntityPropertyScriptField {
	/**
	 * @param ScriptState $scriptState
	 * @return \rocket\script\entity\filter\FilterItem 
	 */
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null);
}