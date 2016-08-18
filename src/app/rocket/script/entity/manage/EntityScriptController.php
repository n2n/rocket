<?php
namespace rocket\script\entity\manage;

use n2n\http\PageNotFoundException;
use n2n\http\ControllerAdapter;
use n2n\http\ForbiddenException;
use rocket\script\core\ManageState;

class EntityScriptController extends ControllerAdapter {
		
	public function index(ManageState $manageState, $commandId, array $cmds, array $contextCmds) {		
		$scriptState = $manageState->peakScriptState();
		
		$commands = $scriptState->getScriptMask()->getCommands();
		if (!isset($commands[$commandId])) {
			throw new PageNotFoundException();
		}
		
		$command = $commands[$commandId];
		
		if (!$scriptState->isScriptCommandAvailable($command)) {
			throw new ForbiddenException();
		}
		
		$scriptState->setExecutedScriptCommand($command);
		
		array_push($contextCmds, array_shift($cmds));
		$command->createController($scriptState)
				->execute($cmds, $contextCmds, $this->getN2nContext());
	}
}