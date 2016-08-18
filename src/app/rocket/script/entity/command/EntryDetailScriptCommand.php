<?php
namespace rocket\script\entity\command;

use rocket\script\entity\manage\ScriptNavPoint;

interface EntryDetailScriptCommand {
	public function getEntryDetailPathExt(ScriptNavPoint $scriptNavPoint);
}