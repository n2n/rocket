<?php
namespace rocket\script\entity\manage\mapping;

use rocket\script\entity\command\ScriptCommand;
use rocket\script\entity\manage\security\PrivilegeBuilder;

class WhitelistAccessRestrictor implements AccessRestrictor {
	private $privileges = array();
		
	public function addCommand(ScriptCommand $command, $privilegeExt = null) {
		$this->addPrivilege(PrivilegeBuilder::buildPrivilege($scriptCommand, $privilegeExt));
	}
	
	public function addPrivilege($privilege) {
		$this->privileges[] = (string) $privilege;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\AccessRestrictor::isAccessableBy()
	 */
	public function isAccessableBy(ScriptCommand $command, $privilegeExt = null) {
		if (!PrivilegeBuilder::isPrivilegeCommand($command)) {
			return true;
		}
		
		return PrivilegeBuilder::testPrivileges(PrivilegeBuilder::buildPrivilege($command, $privilegeExt),
				$this->privileges);
	}
}