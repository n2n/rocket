<?php

namespace rocket\script\security;

interface ScriptGrant {
	/**
	 * @return \n2n\util\Attributes 
	 */
	public function getAccessAttributes();
	/**
	 * @return \rocket\script\security\PrivilegesGrant[]
	 */
	public function getPrivilegesGrants();
}