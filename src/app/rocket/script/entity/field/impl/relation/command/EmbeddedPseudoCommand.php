<?php

namespace rocket\script\entity\field\impl\relation\command;

use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\manage\ScriptState;
use n2n\http\ControllerAdapter;

class EmbeddedPseudoCommand extends ScriptCommandAdapter {
/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\ScriptCommand::createController()
	 */
	public function createController(ScriptState $scriptState) {
		return new EmbeddedPseudoController();
	}

}

class EmbeddedPseudoController extends ControllerAdapter {
	
}