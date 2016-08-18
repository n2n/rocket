<?php
namespace rocket\user\model;

use rocket\user\bo\UserGroup;
use rocket\script\core\Script;
use rocket\user\bo\UserScriptGrant;
use rocket\script\entity\EntityScript;

class GroupGrantsViewModel {
	private $userGroup;
	private $items = array();
	
	
	public function __construct(UserGroup $userGroup, array $scripts) {
		$this->userGroup = $userGroup;
		
		$assignedGrants = array();
		foreach ($userGroup->getUserScriptGrants() as $userScriptGrant) {
			$assignedGrants[$userScriptGrant->getScriptId()] = $userScriptGrant;
		}
		
		foreach ($scripts as $script) {
			if ($script instanceof EntityScript && $script->hasSuperEntityScript()) continue;
			$scriptId = $script->getId();
			if (!isset($assignedGrants[$scriptId])) {
				$this->items[$scriptId] = new GroupGrantItem($script);
				continue;
			}
			
			$this->items[$scriptId] = new GroupGrantItem($script, $assignedGrants[$scriptId]);
		}
	}
	
	public function getGroupId() {
		return $this->userGroup->getId();
	}
	
	public function getUserGroup() {
		return $this->userGroup;
	}
	/**
	 * @return \rocket\user\model\GroupGrantItem[]
	 */
	public function getItems() {
		return $this->items;
	}
}

class GroupGrantItem {
	const ACCESS_TYPE_DENIED = 'denied';
	const ACCESS_TYPE_RESTRICTED = 'restricted';
	const ACCESS_TYPE_FULL = 'full';
	
	private $grant;
	private $accessType = self::ACCESS_TYPE_DENIED;
	
	public function __construct(Script $script, UserScriptGrant $grant = null) {
		$this->script = $script;
		
		if ($grant === null) return;
			
		$this->accessType = $grant->isFull() ? self::ACCESS_TYPE_FULL : self::ACCESS_TYPE_RESTRICTED;
	}
	
	public function getId() {
		return $this->script->getId();
	}
	
	public function getLabel() {
		return $this->script->getLabel();
	}
	
	public function getAccessType() {
		return $this->accessType;
	}
}