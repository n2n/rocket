<?php
namespace rocket\script\entity\security;

use rocket\script\entity\command\ScriptCommand;
use rocket\script\security\ScriptConstraint;

interface EntityScriptConstraint extends ScriptConstraint {
	/**
	 * Checks if this SecurityManager allowes the use of passed command.
	 * 
	 * @param ScriptCommand $command
	 * @return boolean Returns true if there could be entities which are accessable by passed command.
	 */
	public function isScriptCommandAvailable(ScriptCommand $command);
	/**
	 * @param \ArrayAccess $values
	 * @return \rocket\script\entity\manage\mapping\AccessRestrictor
	 */
	public function createAccessRestrictor(\ArrayAccess $values);
	/**
	 * @param ScriptCommand $command
	 * @param string $privilegeExt
	 * @return \rocket\script\entity\manage\security\CommandExecutionConstraint
	 */
	public function createCommandExecutionConstraint(ScriptCommand $command, $privilegeExt = null);
}