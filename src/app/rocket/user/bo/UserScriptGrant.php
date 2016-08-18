<?php
namespace rocket\user\bo;

use n2n\util\Attributes;
use n2n\persistence\orm\EntityAdapter;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityAnnotations;
use n2n\util\StringUtils;
use n2n\persistence\orm\CascadeType;
use rocket\script\security\ScriptGrant;

class UserScriptGrant extends EntityAdapter implements ScriptGrant {
	private static function _annotations(AnnotationSet $as) {
		$as->c(EntityAnnotations::TABLE, array('name' => 'rocket_user_script_grant'));
		$as->p('userGroup', EntityAnnotations::MANY_TO_ONE, array('targetEntity' => UserGroup::getClass()));
		$as->p('privilegesGrants', EntityAnnotations::ONE_TO_MANY, array('targetEntity' => UserPrivilegesGrant::getClass(),
				'mappedBy' => 'scriptGrant', 'cascade' => CascadeType::ALL));
	}

	private $id;
	private $scriptId;
	private $userGroup;
	private $full;
	private $accessJson = '[]';
	private $privilegesGrants;
	
	public function __construct() {
		$this->privilegesGrants = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
		
	public function getUserGroup() {
		return $this->userGroup;
	}
	
	public function setUserGroup(UserGroup $userGroup) {
		$this->userGroup = $userGroup;
	}
	
	public function getScriptId() {
		return $this->scriptId;
	}
	
	public function setScriptId($scriptId) {
		$this->scriptId = $scriptId;
	}
	
	public function isFull() {
		return $this->full;
	}
	
	public function setFull($full) {
		$this->full = (boolean) $full;
	}
	
	public function readAccessAttributes() {
		return new Attributes(StringUtils::jsondecode($this->accessJson, true));
	}
	
	public function writeAccessAttributes(Attributes $accessAttributes) {
		$this->accessJson = StringUtils::jsonEncode($accessAttributes->toArray());
	}
	
	public function getAccessAttributes() {
		return $this->readAccessAttributes();
	}
	
	public function getPrivilegesGrants() {
		return $this->privilegesGrants;
	}
	
	public function setPrivilegeGrants(\ArrayObject $privilegeGrants) {
		$this->privilegesGrants = $privilegeGrants;
	}
}