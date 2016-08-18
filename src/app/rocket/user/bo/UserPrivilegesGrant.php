<?php

namespace rocket\user\bo;

use n2n\persistence\orm\EntityAdapter;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityAnnotations;
use n2n\util\StringUtils;
use rocket\script\entity\filter\data\FilterData;
use rocket\script\security\PrivilegesGrant;

class UserPrivilegesGrant extends EntityAdapter implements PrivilegesGrant {
	private static function _annotations(AnnotationSet $as) {
		$as->c(EntityAnnotations::TABLE, array('name' => 'rocket_user_privileges_grant'));
		$as->p('scriptGrant', EntityAnnotations::MANY_TO_ONE, array('targetEntity' => UserScriptGrant::getClass()));
	}
	
	private $id;
	private $scriptGrant;
	private $privilegesJson = '[]';
	private $restricted = false;
	private $restrictionJson = '[]';
	
	
	public function getScriptGrant() {
		return $this->scriptGrant;
	}

	public function setScriptGrant(UserScriptGrant $scriptGrant) {
		$this->scriptGrant = $scriptGrant;
	}

	public function getPrivileges() {
		return StringUtils::jsonDecode($this->privilegesJson, true);
	}
	
	public function setPrivileges(array $privileges) {
		$this->privilegesJson = StringUtils::jsonEncode($privileges);
	}
	
	public function isRestricted() {
		return (boolean)$this->restricted;
	}
	
	public function setRestricted($restricted) {
		$this->restricted = (boolean)$restricted;
	}
	
	public function readRestrictionFilterData() {
		return FilterData::createFromArray(StringUtils::jsonDecode($this->restrictionJson, true));
	}
	
	public function writeRestrictionFilterData(FilterData $restrictionFilterData) {
		$this->restrictionJson = StringUtils::jsonEncode($restrictionFilterData->toArray());
	}
	
	public function getRestrictionFilterData() {
		if (!$this->isRestricted()) return null;
		
		$filterData = $this->readRestrictionFilterData();
		
		if ($filterData->isEmpty()) return null;
		return $filterData;
	}
}