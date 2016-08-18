<?php
namespace rocket\user\model;

use n2n\persistence\orm\OrmUtils;
use rocket\user\bo\User;
use n2n\persistence\orm\EntityManager;
use n2n\model\RequestScoped;
use rocket\user\bo\Login;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\N2N;
use rocket\user\bo\UserGroup;
use rocket\user\bo\UserScriptGrant;

class UserDao implements RequestScoped {
	/**
	 * @var \n2n\persistence\orm\EntityManager
	 */
	private $em;
	/**
	 * @param \n2n\persistence\orm\EntityManager $em
	 */
	private function _init(EntityManager $em) {
		$this->em = $em;
	}	
	/**
	 * @return \rocket\user\bo\User[]
	 */
	public function getUsers() {
		return $this->em
				->createSimpleCriteria(User::getClass(), null, array('id' => 'ASC'))
				->fetchArray();
	}
	/**
	 * @param string $nick
	 * @param string $password
	 * @return \rocket\user\bo\User
	 */
	public function getUserByNickAndPassword($nick, $password) {
		return $this->em
				->createSimpleCriteria(User::getClass(), array('nick' => $nick, 'password' => $password))
				->fetchSingle();
	} 
	/**
	 * @param int $id
	 * @return \rocket\user\bo\User
	 */
	public function getUserById($id) {
		return $this->em->find(User::getClass(), $id);
	}
	/**
	 * @param \rocket\user\bo\User $user
	 */
	public function saveUser(User $user) {
		$tx = N2N::createTransaction();
		$this->em->persist($user);
		$tx->commit();
	}
	/**
	 * @param \rocket\user\bo\User $user
	 */
	public function deleteUser(User $user) {
		$tx = N2N::createTransaction();
		$this->em->remove($user);
		$tx->commit();
	}
	
	public function saveUserGroup(UserGroup $userGroup) {
		$tx = N2N::createTransaction();
		$this->em->persist($userGroup);
		$tx->commit();
	}
	
	public function getUserGroupById($id) {
		return $this->em->find(UserGroup::getClass(), $id);
	}
	
	public function containsNick($nick) {
		return (bool) OrmUtils::createCountCriteria($this->em, User::getClass(), array('nick' => $nick))->fetchSingle();
	}
	
	public function getUserGroups() {
		return $this->em->createSimpleCriteria(UserGroup::getClass(), null, array('name' => 'ASC'))->fetchArray();
	}
	
	public function removeUserGroup(UserGroup $userGroup) {
		return $this->em->remove($userGroup);
	}
	
	public function removeUserScriptGrant(UserScriptGrant $userScriptGrant) {
		return $this->em->remove($userScriptGrant);
	}
	/**
	 * @param string $nick
	 * @param string $rawPassword
	 * @param \rocket\user\bo\User $user
	 */
	public function createLogin($nick, $rawPassword, User $user = null) {
		$login = new Login();
		$login->setNick($nick);
		$login->setWrongPassword(isset($user) ? null : $rawPassword);
		$login->setType(isset($user) ? $user->getType() : null);
		$login->setSuccessfull(isset($user));
		$login->setIp($_SERVER['REMOTE_ADDR']);
		$login->setDatetime(new \DateTime());
		$this->em->persist($login);
		return $login;
	}
	/**
	 * @return \rocket\user\bo\Login[]
	 */
	public function getSuccessfullLogins($limit = null, $num = null) {
		return $this->em->createSimpleCriteria(Login::getClass(), array('successfull' => true), 
				array('dateTime' => 'DESC'), $limit, $num)->fetchArray();
	}
	/**
	 * @return \rocket\user\bo\Login[]
	 */
	public function getFailedLogins() {
		return $this->em->createSimpleCriteria(Login::getClass(), array('successfull' => false), 
				array('dateTime' => 'DESC'))->fetchArray();
	}
	
	public function getCountOfLatestFailedLoginsForCurrentIp() {
		$oneHourBefore = new \DateTime();
		$oneHourBefore->sub(new \DateInterval('PT1H'));
		$criteria = OrmUtils::createCountCriteria($this->em, Login::getClass(), array('ip' => $_SERVER['REMOTE_ADDR'], 
				'successfull' => false));
		$criteria->where()->andMatch(new CriteriaProperty(array(EntityManager::SIMPLE_CRITERIA_ENTITY_ALIAS, 'dateTime')), CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, $oneHourBefore);
		return $criteria->fetchSingle();
	}
	
	public function deleteFailedLogins() {
		foreach ($this->getFailedLogins() as $login) {
			$this->em->remove($login);
		}
	}
	
	public static function buildPassword($rawPassword) {
		return crypt($rawPassword, '$2a$07$holeradioundholeradiohill$');
	}
	
}