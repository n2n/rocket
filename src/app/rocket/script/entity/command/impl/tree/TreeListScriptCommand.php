<?php
namespace rocket\script\entity\command\impl\tree;

use rocket\script\entity\command\impl\tree\controller\TreeListController;
use rocket\script\entity\command\OverviewScriptCommand;
use rocket\script\entity\command\impl\IndependentScriptCommandAdapter;
use n2n\util\Attributes;
use rocket\script\entity\manage\ScriptState;

class TreeListScriptCommand extends IndependentScriptCommandAdapter implements OverviewScriptCommand {
	const ID_BASE = 'overview';
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
	}
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getOverviewPathExt() {
		return $this->getId();
	}
	
	public function getTypeName() {
		return 'Tree List (Rocket)';
	}
	
	public function createController(ScriptState $scriptState) {
		$treeListController = new TreeListController();
		TreeUtils::initializeController($this, $treeListController);
		return $treeListController;
	}
}