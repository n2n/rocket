<?php
namespace rocket\user\model;

use n2n\dispatch\val\ValEnum;
use n2n\dispatch\val\ValIsset;
use n2n\core\MessageCode;
use n2n\dispatch\map\BindingErrors;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\DispatchAnnotations;
use rocket\user\bo\User;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\Dispatchable;

class UserForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	protected $user;
	protected $rawPassword;
	protected $rawPassword2;
	protected $type;
	protected $userGroupIds = array();
		
	private $availableUserGroups;
	private $new;
	private $modifyTypeAllowed;
	
	public function __construct(User $user, array $availableUserGroups = null) {
		$this->user = $user;
		$this->modifyTypeAllowed = $this->isNew();
		$this->type = $user->getType();
		
		foreach ($this->user->getUserGroups() as $userGroup) {
			$this->userGroupIds[$userGroup->getId()] = $userGroup->getId();
		}
		
		if (isset($availableUserGroups)) {
			$this->availableUserGroups = array();
			foreach ($availableUserGroups as $userGroup) {
				$this->availableUserGroups[$userGroup->getId()] = $userGroup;
			}
		}
	}
	
	public function setModifyTypeAllowed($modifyTypeAllowed) {
		$this->modifyTypeAllowed = $modifyTypeAllowed;
	}
	
	public function isModifyTypeAllowed() {
		return $this->modifyTypeAllowed;
	}
	
	public function isNew() {
		return $this->user->getId() === null;
	}
	
	public function setRawPassword($rawPassword) {
		$this->rawPassword = $rawPassword;
	}
	
	public function getRawPassword() {
		return $this->rawPassword;
	}
	
	public function setRawPassword2($rawPassword2) {
		$this->rawPassword2 = $rawPassword2;
	}
	
	public function getRawPassword2() {
		return $this->rawPassword2;
	}
	
	public function setUser(User $user) {
		$this->user = $user;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function getType() {
		return $this->type;
	} 
	
	public function getTypeOptions() {
		return array(User::TYPE_NONE => 'Default', User::TYPE_ADMIN => 'Admin', 
				User::TYPE_SUPER_ADMIN => 'Super Admin');
	}
	
	public function areGroupsReadOnly() {
		return null === $this->availableUserGroups;
	}
	
	public function getAvaialableUserGroups() {
		return $this->availableUserGroups;
	}
	
	public function getUserGroupIds() {
		return $this->userGroupIds;
	}
	
	public function setUserGroupIds(array $userGroupIds) {
		$this->userGroupIds = $userGroupIds;
	}
	
	private function _validation(BindingConstraints $bc) { 
		if ($this->isNew()) {
			$bc->val('rawPassword', new ValIsset());
		}
		
		$editedUser = $this->user;
		$bc->addClosureValidator(function($user, BindingErrors $be, UserDao $userDao) use ($editedUser) {
			if ($editedUser->getNick() != $user->nick && $userDao->containsNick($user->nick)) {
				$be->addError('nick', new MessageCode('user_taken_nick_err', array('nick' => $user->nick)));
			}
		});
		
		$bc->addClosureValidator(function($rawPassword, $rawPassword2, BindingErrors $be) {
			if ($rawPassword !== $rawPassword2) {
				$be->addError('rawPassword', new MessageCode('user_passwords_do_not_equal_err'));
			}
		});
		
		$bc->val('type', new ValEnum(User::getTypes()));
		
		if (isset($this->availableUserGroups)) {
			$bc->val('userGroupIds', new ValEnum(array_keys($this->availableUserGroups)));
		}
	}
	
	public function save() {
		if (null !== ($rawPassword = $this->getRawPassword())) {
			$this->user->setPassword(UserDao::buildPassword($rawPassword));
		}
		
		if ($this->isModifyTypeAllowed()) {
			$this->user->setType($this->getType());
		}
		
		if (isset($this->availableUserGroups)) {
			$userGroups = new \ArrayObject();
			foreach ($this->userGroupIds as $userGroupId) {
				$userGroups[] = $this->availableUserGroups[$userGroupId];
			}
			$this->user->setUserGroups($userGroups);
		}
	}
}