<?php

namespace rocket\script\entity\field;

use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use n2n\util\Attributes;
use n2n\core\N2nContext;

interface AccessControllableScriptField extends ScriptField {
	/**
	 * @return OptionCollection 
	 */
	public function createAccessOptionCollection(N2nContext $n2nContext);
	/**
	 * @param ScriptState $scriptState
	 * @param ScriptSelection $scriptSelection
	 * @param Attributes $accessAttributes
	 * @return boolean
	 */
	public function isWritingAllowed(Attributes $accessAttributes, ScriptState $scriptState, 
			ScriptSelection $scriptSelection = null);
}