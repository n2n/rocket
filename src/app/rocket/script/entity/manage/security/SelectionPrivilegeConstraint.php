<?php
// namespace rocket\script\entity\manage\security;

// use rocket\script\entity\command\ScriptCommand;
// use rocket\script\entity\security\ScriptSecurityManager;
// use rocket\script\entity\manage\mapping\MappingValidationResult;
// use n2n\core\MessageCode;
// use rocket\script\entity\filter\SelectorValidationResult;

// class SelectionPrivilegeConstraint {
// 	private $accessGrants;
// 	private $grantedPrivileges;

// 	public function __construct(array $accessGrants, array $grantedPrivileges) {
// 		$this->accessGrants = $accessGrants;
// 		$this->grantedPrivileges = $grantedPrivileges;
// 	}

// 	public function containsAccessablePrivilege(ScriptCommand $scriptCommand, $privilegeExt = null) {
// 		return ScriptSecurityManager::containsAccessablePrivilege($this->grantedPrivileges,
// 				$scriptCommand, $privilegeExt);
// 	}
	
// 	public function getAccessGrants() {
// 		return $this->accessGrants;
// 	}
	
// 	public function getGrantedPrivileges() {
// 		return $this->grantedPrivileges;
// 	}
	
// 	public function validateValues(\ArrayAccess $values, MappingValidationResult $mappingValidationResult) {
// 		$validationResults = array();
// 		foreach ($this->accessGrants as $accessGrants) {
// 			$validationResult = new SelectorValidationResult();
// 			if ($accessGrants->validateValues($values, $validationResult)) {
// 				return true;
// 			}
// 			$validationResults[] = $validationResult;
// 		}
		
// 		$mappingValidationResult->addError(null, new MessageCode('no_access_to_values'));
		
// 		return false;
// 	}
	
// 	public function acceptsValue($id, $value) {
// 		foreach ($this->accessGrants as $accessGrant) {
// 			if ($accessGrant->acceptsValue($id, $value)) return true;
// 		}
		
// 		return false;
// 	}
	
// 	private function combine(array $valdationResults, $mappingValidationResult) {
// 		$mappingValidationResult->addMessage(null, new MessageCode('no_access_to_values'));
// // 		foreach ($valdationResults as $validationResult) {
// // 			$mappingValidationResult->add
// // 		}
// 	}
// }