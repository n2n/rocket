<?php

namespace rocket\script\entity\security;

use rocket\script\entity\filter\SelectorModel;
use n2n\core\N2nContext;
use rocket\script\entity\EntityScript;
use rocket\script\entity\command\ScriptCommand;
use rocket\script\entity\command\PrivilegedScriptCommand;
use rocket\script\entity\command\PrivilegeExtendableScriptCommand;
use rocket\script\entity\manage\mapping\WhitelistAccessRestrictor;
use rocket\script\entity\manage\security\PrivilegeBuilder;
use rocket\script\security\ScriptGrant;
use rocket\script\entity\filter\FilterModel;

class CommonEntityScriptConstraint implements EntityScriptConstraint {
	private $entityScript;
	private $n2nContext;
	private $accessAttributes = array();
	private $privilegeGrants = array();
	private $privilegesGrantItems = array();
	private $selectorItems;
	private $filterModel;
	private $selectorModel;
	
	public function __construct(EntityScript $entityScript, N2nContext $n2nContext) {
		$this->entityScript = $entityScript;
		$this->n2nContext = $n2nContext;
	}
	
	public function getAccessAttributes() {
		return $this->accessAttributes;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\security\ScriptConstraint::getPrivilegesGrants()
	*/
	public function getPrivilegesGrants() {
		return $this->privilegesGrants;
	}
	
	public function addScriptGrant(ScriptGrant $scriptGrant) {
		$this->accessAttributes[] = $scriptGrant->getAccessAttributes();
		foreach ($scriptGrant->getPrivilegesGrants() as $privilegeGrant) {
			$this->privilegesGrantItems[] = new PrivilegesGrantItem($privilegeGrant->getPrivileges(),
					$privilegeGrant->getRestrictionFilterData(), $this);
		}
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityConstraint::isScriptCommandAvailable()
	 */
	public function isScriptCommandAvailable(ScriptCommand $scriptCommand, $privilegeExt = null) {
		if ($privilegeExt === null && !($scriptCommand instanceof PrivilegedScriptCommand
				|| $scriptCommand instanceof PrivilegeExtendableScriptCommand)) {
			return true;
		}
		
		$privilege = PrivilegeBuilder::buildPrivilege($scriptCommand, $privilegeExt);
		foreach ($this->privilegesGrantItems as $item) {
			if ($item->isPrivilegeAccessable($privilege)) {
				return true;
			}
		}
		
		return false;
	}
	
	private function getSelectorItems() {
		if ($this->selectorItems === null) {
			$this->selectorItems = $this->entityScript->createRestrictionSelectorItems($this->n2nContext);
		}
		
		return $this->selectorItems;
	}
	
	public function getOrCreateFilterModel() {
		if ($this->filterModel === null) {
			$this->filterModel = FilterModel::createFromFilterItems($this->getSelectorItems());
		}
		
		return $this->filterModel;
	}
	
	public function getOrCreateSelectorModel() {
		if ($this->selectorModel === null) {
			$this->selectorModel = SelectorModel::createFromSelectorItems($this->getSelectorItems());
		}
		
		return $this->selectorModel;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityConstraint::createAccessRestrictor()
	 */
	public function createAccessRestrictor(\ArrayAccess $values) {
		$accessRestirctor = new WhitelistAccessRestrictor();
		foreach ($this->privilegesGrantItems as $privilegeGrantItem) {
			if (!$privilegeGrantItem->acceptsValues($values)) continue;
			
			foreach ($privilegeGrantItem->getPrivileges() as $privilege) {
				$accessRestirctor->addPrivilege($privilege);
			}
		}
		return $accessRestirctor;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\manage\security\SecurityConstraint::createCommandExecutionConstraint()
	 */
	public function createCommandExecutionConstraint(ScriptCommand $command, $privilegeExt = null) {
		$privilege = null;
		if ($command instanceof PrivilegedScriptCommand
				|| $command instanceof PrivilegeExtendableScriptCommand) {
			$privilege = PrivilegeBuilder::buildPrivilege($command, $privilegeExt);
		}
		
		$items = array();
		foreach ($this->privilegesGrantItems as $item) {
			if ($privilege !== null && !$item->isPrivilegeAccessable($privilege)) continue;
			
			if (!$item->isRestricted()) {
				return new EmptyCommandExecutionConstraint();
			}
		
			$items[] = $item;
		}
		
		return new ScriptCommandExecutionConstraint($items);
	}	
}

