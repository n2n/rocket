<?php
namespace rocket\user\bo;

use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\val\ValEmail;
use n2n\dispatch\val\ValIsset;
use n2n\dispatch\map\BindingConstraints;
use n2n\persistence\orm\EntityAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\reflection\ArgumentUtils;
use n2n\persistence\orm\EntityAdapter;
use n2n\dispatch\Dispatchable;
use rocket\script\security\FullAccessSecurityManager;
use rocket\script\security\ScriptSecurityManager;
use n2n\core\N2nContext;

class User extends EntityAdapter implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(EntityAnnotations::TABLE, array('name' => 'rocket_user'));
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, 
				array('names' => array('nick', 'firstname', 'lastname', 'email')));
		$as->p('userGroups', EntityAnnotations::MANY_TO_MANY, array('targetEntity' => UserGroup::getClass()));
	}
	
	const TYPE_SUPER_ADMIN = 'superadmin';
	const TYPE_ADMIN = 'admin';
	const TYPE_NONE = null;
	
	private $id;
	private $nick;
	private $password;
	private $firstname;
	private $lastname;
	private $email;
	private $type;
	private $userGroups;
	
	public function __construct() {
		$this->userGroups = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getNick() {
		return $this->nick;
	}
	
	public function setNick($nick) {
		$this->nick = $nick;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
	
	public function getFirstname() {
		return $this->firstname;
	}
	
	public function setFirstname($firstname) {
		$this->firstname = $firstname;
	}
	
	public function getLastname() {
		return $this->lastname;
	}
	
	public function setLastname($lastname) {
		$this->lastname = $lastname;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function setType($type) {
		ArgumentUtils::validateEnum($type, self::getTypes());
		$this->type = $type;
	} 
	
	public function getType() {
		return $this->type;
	}
	
	public function isSuperAdmin() {
		return $this->type == self::TYPE_SUPER_ADMIN;
	}
	
	public function isAdmin() {
		return $this->type == self::TYPE_ADMIN || $this->isSuperAdmin();
	}
	/**
	 * @param \rocket\user\bo\UserGroup[] $userGroups
	 */
	public function setUserGroups(\ArrayObject $userGroups) {
		$this->userGroups = $userGroups;
	}
	/**
	 * @return \rocket\user\bo\UserGroup[]
	 */
	public function getUserGroups() {
		return $this->userGroups;
	}
	
	public static function getTypes() {
		return array(self::TYPE_SUPER_ADMIN, self::TYPE_ADMIN, null);
	}
	
	public function equals($user) {
		return $user instanceof User && $this->getId() == $user->getId();
	}
	
	public function __toString() {
		$str = $this->getFirstname();
		if (null !== ($lastname = $this->getLastname())) {
			if ($str) $str .= ' ';
			$str .= $lastname;
		} 
		
		if (!$str) {
			$str = $this->getNick();
		}
		
		return $str;
	}
	
	public function createSecurityManager(N2nContext $n2nContext) {
		if ($this->isAdmin()) {
			return new FullAccessSecurityManager();
		}
		
		$securityManager = new ScriptSecurityManager($n2nContext);
		foreach ($this->getUserGroups() as $userGroup) {
			$securityManager->addAccessableMenuItemIds($userGroup->getAccessableMenuItemIds());
			
			foreach ($userGroup->getUserScriptGrants() as $scriptGrant) {
				if ($scriptGrant->isFull()) {
					$securityManager->addFullAccessableScriptId($scriptGrant->getScriptId());
				} else {
					$securityManager->addScriptGrant($scriptGrant->getScriptId(), $scriptGrant);
				}
			}
		}
		
		return $securityManager;
	}
	
	private function _validation(BindingConstraints $bc) { 
		$bc->val('nick', new ValIsset());
		$bc->val('email', new ValEmail(false));
	}
}
