<?php
namespace rocket\script\entity\field;

use n2n\util\Attributes;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\display\Editable;

interface EditableScriptField extends DisplayableScriptField {
	/**
	 * @param ScriptState $scriptState
	 * @param Attributes $maskAttributes
	 * @return Editable
	 */
	public function createEditable(ScriptState $scriptState, Attributes $maskAttributes);
}