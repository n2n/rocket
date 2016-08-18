<?php

namespace rocket\script\entity\field;

use rocket\script\entity\manage\ScriptState;
use n2n\util\Attributes;
interface DisplayableScriptField extends ScriptField {
	/**
	 * @return bool
	 */
	public function isDisplayInListViewEnabled();
	/**
	 * @return bool
	*/
	public function isDisplayInDetailViewEnabled();
	/**
	 * @return bool
 	 */
	public function isDisplayInEditViewEnabled();
	/**
	 * @return bool 
	 */
	public function isDisplayInAddViewEnabled();
	
	public function createDisplayable(ScriptState $scriptState, Attributes $maskAttributes);
}