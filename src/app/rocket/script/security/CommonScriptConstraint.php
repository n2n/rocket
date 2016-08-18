<?php

namespace rocket\script\security;

use n2n\reflection\ArgumentUtils;

class CommonScriptConstraint implements ScriptConstraint {
	protected $accessAttributes;
	protected $privilegesGrants;
	
	public function addScriptGrant(ScriptGrant $scriptGrant) {
		$this->accessAttributes[] = $scriptGrant->getAccessAttributes();
		foreach ($scriptGrant->getPrivilegesGrants() as $privilegeGrant) {
			$this->privilegesGrants[] = $privilegeGrant;
		}
	}
	
	public function setAccessAttributes(array $accessAttributes) {
		ArgumentUtils::validateArrayType($accessAttributes, 'n2n\util\Attributes');
		$this->accessAttributes = $accessAttributes;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\security\ScriptConstraint::getAccessAttributes()
	 */
	public function getAccessAttributes() {
		return $this->accessAttributes;
	}
	/**
	 * @param array $privilegesGrants
	 */
	public function setPrivilegesGrants(array $privilegesGrants) {
		ArgumentUtils::validateArrayType($accessAttributes, 'rocket\script\security\PrivilegesGrant');
		$this->privilegesGrants[] = $privilegesGrants;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\security\ScriptConstraint::getPrivilegesGrants()
	 */
	public function getPrivilegesGrants() {
		return $this->privilegesGrants;
	}

}