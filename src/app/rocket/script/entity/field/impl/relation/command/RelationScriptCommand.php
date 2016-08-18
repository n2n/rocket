<?php
namespace rocket\script\entity\field\impl\relation\command;

use rocket\script\entity\command\impl\ScriptCommandAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\field\impl\relation\model\ScriptFieldRelation;
use rocket\script\entity\field\impl\relation\command\controller\RelationController;

class RelationScriptCommand extends ScriptCommandAdapter {
	const ID_BASE = 'rl';
	
	private $scriptFieldRelation;
	
	public function __construct(ScriptFieldRelation $scriptFieldRelation) {
		$this->scriptFieldRelation = $scriptFieldRelation;
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getTypeName() {
		return 'OneTo';
	}

	public function createController(ScriptState $scriptState) {
		return new RelationController($this->scriptFieldRelation);
	}
}