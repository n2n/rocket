<?php
namespace rocket\script\security;

interface PrivilegesGrant {
	/**
	 * @return array 
	 */
	public function getPrivileges();
	/**
	 * @return \rocket\script\entity\filter\data\FilterData
	 */
	public function getRestrictionFilterData();
}