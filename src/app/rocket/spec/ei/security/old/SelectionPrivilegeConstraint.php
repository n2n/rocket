<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
// namespace rocket\spec\ei\manage\security;

// use rocket\spec\ei\component\command\EiCommand;
// use rocket\spec\ei\security\ScriptSecurityManager;
// use rocket\spec\ei\manage\mapping\MappingValidationResult;
// use n2n\l10n\MessageCode;
// use rocket\spec\ei\manage\critmod\SelectorValidationResult;

// class SelectionPrivilegeConstraint {
// 	private $accessGrants;
// 	private $grantedPrivileges;

// 	public function __construct(array $accessGrants, array $grantedPrivileges) {
// 		$this->accessGrants = $accessGrants;
// 		$this->grantedPrivileges = $grantedPrivileges;
// 	}

// 	public function containsaccessiblePrivilege(EiCommand $eiCommand, $privilegeExt = null) {
// 		return ScriptSecurityManager::containsaccessiblePrivilege($this->grantedPrivileges,
// 				$eiCommand, $privilegeExt);
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
