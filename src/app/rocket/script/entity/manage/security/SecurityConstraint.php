<?php
namespace rocket\script\entity\manage\security;

use rocket\script\entity\command\ScriptCommand;

interface SecurityConstraint {
	
	public function isScriptCommandAvailable(ScriptCommand $scriptCommand, $privilegeExt = null);
	/**
	 * @return \n2n\util\Attributes[] 
	 */
	public function getAccessAttributes();
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