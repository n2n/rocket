<?php
namespace rocket\script\entity\command\impl;

use rocket\script\entity\command\ScriptCommand;
use rocket\script\entity\ScriptElementAdapter;

abstract class ScriptCommandAdapter extends ScriptElementAdapter implements ScriptCommand {
	public function equals($obj) {
		return $obj instanceof ScriptCommand && parent::equals($obj);
	}
}