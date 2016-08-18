<?php
namespace rocket\script\entity\command;

use rocket\script\entity\ScriptElement;
use rocket\script\entity\manage\ScriptState;

interface ScriptCommand extends ScriptElement {
	/**
	 * @param ScriptState $scriptState
	 * @return Controller
	 */
	public function createController(ScriptState $scriptState);
	/**
	 * @param mixed $obj
	 * @return boolean
	 */
	public function equals($obj);
}