<?php
namespace rocket\script\entity\manage\mapping;

use rocket\script\entity\command\ScriptCommand;

class BlacklistAccessRestrictor implements AccessRestrictor {
	private $blacklist = array();
		
	public function addToBlacklist(ScriptCommand $command, $privilegeExt = null) {
		$this->blacklist[] = array('command' => $command, 'privilegeExt' => $privilegeExt);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\mapping\AccessRestrictor::isAccessableBy()
	 */
	public function isAccessableBy(ScriptCommand $command, $privilegeExt = null) {
		foreach ($this->blacklist as $blacklist) {
			if ($command->equals($blacklist['command']) 
					&& (!isset($blacklist['privilegeExt']) || $blacklist['privilegeExt'] == $privilegeExt)) {
				return false;
			}
		}
		
		return true;
	}

	
}