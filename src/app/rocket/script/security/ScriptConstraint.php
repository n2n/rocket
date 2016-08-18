<?php
namespace rocket\script\security;

interface ScriptConstraint {
	/**
	 * @return \n2n\util\Attributes[]
	 */
	public function getAccessAttributes();
	/**
	 * @return \rocket\script\security\PrivilegesGrant[] 
	 */
	public function getPrivilegesGrants();
}