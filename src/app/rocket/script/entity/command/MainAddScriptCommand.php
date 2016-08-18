<?php
namespace rocket\script\entity\command;

use n2n\http\Response;
use n2n\http\Request;
use rocket\script\entity\manage\ScriptNavPoint;
use rocket\script\entity\manage\ScriptSelection;

interface EntryAddScriptCommand {
	public function createControllerWithCallback(Request $request, Response $response, MainAddCallback $callback);
	public function getEntryDetailPathExt(ScriptNavPoint $scriptNavPoint);
} 

interface MainAddCallback {
	public function createPath(Request $request, ScriptSelection $scriptSelection);
}