<?php
namespace rocket\user\model;

use n2n\core\MessageContainer;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnotationSet;
use n2n\model\SessionScoped;

class LoginContext implements SessionScoped, Dispatchable {
	const MAX_LOGIN_ATTEMPTIONS = 5;
	
	private static function _annotations(AnnotationSet $as) {
		$as->m('login', DispatchAnnotations::MANAGED_METHOD);
	}
	
	protected $nick;
	protected $rawPassword;
	
	private $currentUserId;
	private $userDao;
	
	private function _init(UserDao $userDao) {
		$this->userDao = $userDao;
	}
	
	private function _onSerialize() {
		$this->userDao = null;
	}
	
	private function _onUnserialize(UserDao $userDao) {
		$this->userDao = $userDao;
	}
	
	public function getNick() {
		return $this->nick;
	}
	
	public function setNick($nick) {
		$this->nick = $nick;
	}
	
	public function setRawPassword($rawPassword) {
		$this->rawPassword = $rawPassword;
	}
	
	public function getRawPassword() {
		return $this->rawPassword;
	}
	
	private function _validation() {
		
	}
	
	public function login(MessageContainer $messageContainer) {
		if ($this->userDao->getCountOfLatestFailedLoginsForCurrentIp() >= self::MAX_LOGIN_ATTEMPTIONS) {
			$messageContainer->addErrorCode('user_max_attemptions_reached_err');
			return false;
		} 
		
		$currentUser = $this->userDao->getUserByNickAndPassword(
				$this->getNick(), UserDao::buildPassword($this->getRawPassword()));
		$this->userDao->createLogin($this->getNick(), $this->getRawPassword(), $currentUser);
		if (is_null($currentUser)) {
			$messageContainer->addErrorCode('user_invalid_login_err');
			return false;
		}
		$this->rawPassword = null;
		$this->currentUserId = $currentUser->getId();
		return true;
	}
	
	public function logout() {
		$this->currentUserId = null;
	}
	
	public function hasCurrentUser() {
		return is_object($this->getCurrentUser());
	}
	
	public function getCurrentUser() {
		return $this->userDao->getUserById($this->currentUserId);
	}
}