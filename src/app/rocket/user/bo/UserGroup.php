<?php
namespace rocket\user\bo;

use n2n\persistence\orm\EntityAdapter;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityAnnotations;
use n2n\persistence\orm\CascadeType;
use n2n\util\StringUtils;
use n2n\reflection\ArgumentUtils;
use n2n\core\UnsupportedOperationException;

class UserGroup extends EntityAdapter {
	private static function _annotations(AnnotationSet $as) {
		$as->c(EntityAnnotations::TABLE, array('name' => 'rocket_user_group'));
		$as->p('users', EntityAnnotations::MANY_TO_MANY, array(
				'targetEntity' => User::getClass(), 'mappedBy' => 'userGroups'));
		$as->p('userScriptGrants', EntityAnnotations::ONE_TO_MANY, array(
				'targetEntity' => UserScriptGrant::getClass(),	'mappedBy' => 'userGroup', 
				'cascade' => CascadeType::ALL));
	}
	
	private $id;
	private $name;
	private $users;
	private $navJson = null;
	private $userScriptGrants;
	
	public function __construct() {
		$this->users = new \ArrayObject();
		$this->userScriptGrants = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getUsers() {
		return $this->users;
	}
	
	public function setUsers(\ArrayObject $users) {
		$this->users = $users;
	}
	
	public function isMenuItemAccessRestricted() {
		return $this->navJson !== null;
	}
	/**
	 * @return array if null is returned all MenuItems are accessable.
	 */
	public function getAccessableMenuItemIds() {
		if ($this->navJson === null)  {
			throw new UnsupportedOperationException();
		}
		
		return StringUtils::jsonDecode($this->navJson, true);
	}
	
	public function setAccessableMenuItemIds(array $menuItemIds = null) {
		if ($menuItemIds === null) {
			$this->navJson = null;
			return;
		}
		ArgumentUtils::validateArrayType($menuItemIds, 'scalar');
		$this->navJson = StringUtils::jsonEncode($menuItemIds);
	}
	/**
	 * @return \rocket\user\bo\UserScriptGrant[]
	 */
	public function getUserScriptGrants() {
		return $this->userScriptGrants;
	}
	
	public function setUserScriptGrants(\ArrayObject $userScriptGrants) {
		$this->userScriptGrants = $userScriptGrants;
	}
}