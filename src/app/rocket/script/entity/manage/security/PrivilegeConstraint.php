<?php

namespace rocket\script\entity\manage\security;

class PrivilegeConstraint {
	private $accessGrants;
	private $privilegeGrants;
	
	public function __construct(array $accessGrants, array $privilegeGrants) {
		$this->accessGrants = $accessGrants;
		$this->privilegeGrants = $privilegeGrants;
	}
	
	public function createSelectionPrivilegeConstraint(\ArrayAccess $values = null) {
		$accessGrants = array();
		foreach ($this->accessGrants as $accessGrant) {
			if ($values === null || $accessGrant->acceptsValues($values)) {
				$accessGrants[] = $accessGrant;
			}
		}
		
		$grantedPrivileges = array();
		foreach ($this->privilegeGrants as $privilegeGrants) {
			if ($values === null || $privilegeGrants->acceptsValues($values)) {
				$grantedPrivileges = array_merge($grantedPrivileges, $privilegeGrants->getGrantedPrivileges());
			}
		}
		
		return new SelectionPrivilegeConstraint($accessGrants, $grantedPrivileges);
	}
	
	
// 	public function applyToScriptSelectionMapping(ScriptSelectionMapping $scriptSelectionMapping) {
// 		$values = $scriptSelectionMapping->getValues();
		
		
// 	}
}