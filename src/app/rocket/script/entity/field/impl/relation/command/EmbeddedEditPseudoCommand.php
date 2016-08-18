<?php

namespace rocket\script\entity\field\impl\relation\command;

use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\command\PrivilegedScriptCommand;
use rocket\script\entity\manage\ScriptState;
use n2n\http\ControllerAdapter;

class EmbeddedEditPseudoCommand extends ScriptCommandAdapter implements PrivilegedScriptCommand {
	private $idBase;
	private $privilegeLabel;
	
	public function __construct($privilegeLabel, $relationFieldId, $targetEntityScriptId) {
		$this->idBase = $this->getIdBase() . '-' . $relationFieldId . '-' . $targetEntityScriptId;
		$this->privilegeLabel = $privilegeLabel;
		
	}
	
	public function getIdBase() {
		return $this->idBase; 
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\PrivilegedScriptCommand::getPrivilegeLabel()
	 */
	public function getPrivilegeLabel(\n2n\l10n\Locale $locale) {
		return $this->privilegeLabel;	
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\ScriptCommand::createController()
	 */
	public function createController(ScriptState $scriptState) {
		return new EmbeddedEditPseudoController();
	}

}

class EmbeddedEditPseudoController extends ControllerAdapter {
	
}