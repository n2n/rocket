<?php
namespace rocket\script\entity\manage\mapping;

use rocket\script\entity\command\ScriptCommand;

interface AccessRestrictor {
	public function isAccessableBy(ScriptCommand $scriptCommand, $privilegeExt = null);
}