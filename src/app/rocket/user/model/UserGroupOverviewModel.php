<?php
namespace rocket\user\model;

use rocket\script\core\ScriptManager;
use rocket\user\bo\UserScriptGrant;
class UserGroupOverviewModel {
	private $userGroups;
	private $scriptManager;
	
	public function __construct(array $userGroups, ScriptManager $scriptManager) {
		$this->userGroups = $userGroups;
		$this->scriptManager = $scriptManager;
	}
	
	public function getUserGroups() {
		return $this->userGroups;
	}
	
	public function prettyConstraintName(UserScriptGrant $userScriptGrant) {
		return $this->scriptManager->getScriptById($userScriptGrant->getScriptId())->getLabel();
	}
}