<?php
namespace rocket\script\entity\field;

use rocket\script\entity\field\ScriptField;
use n2n\core\N2nContext;
use rocket\script\entity\manage\ScriptState;

interface SortableScriptField extends ScriptField {
	/**
	 * @param N2nContext $n2nContext
	 * @param ScriptState $scriptState
	 * @return SortItem
	 */
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null);
}