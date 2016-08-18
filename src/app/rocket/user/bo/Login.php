<?php
namespace rocket\user\bo;

use n2n\persistence\orm\EntityAdapter;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityAnnotations;

class Login extends EntityAdapter {
	private static function _annotations(AnnotationSet $as) {
		$as->c(EntityAnnotations::TABLE, array('name' => 'rocket_login'));
	}
	
	private $id;
	private $nick;
	private $wrongPassword;
	private $type;
	private $successfull;
	private $ip;
	private $dateTime;
	/**
	 * @return int $id
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	/**
	 * @return string $nick
	 */
	public function getNick() {
		return $this->nick;
	}
	/**
	 * @param string $nick
	 */
	public function setNick($nick) {
		$this->nick = $nick;
	}
	/**
	 * @return string $password
	 */
	public function getWrongPassword() {
		return $this->wrongPassword;
	}
	/**
	 * @param string $password
	 */
	public function setWrongPassword($wrongPassword) {
		$this->wrongPassword = $wrongPassword;
	}
	/**
	 * @return string $type
	 */
	public function getType() {
		return $this->type;
	}
	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	/**
	 * @return boolean $successfull
	 */
	public function getSuccessfull() {
		return $this->successfull;
	}
	/**
	 * @param boolean $successfull
	 */
	public function setSuccessfull($successfull) {
		$this->successfull = $successfull;
	}
	/**
	 * @return string $ip
	 */
	public function getIp() {
		return $this->ip;
	}
	/**
	 * @param string $ip
	 */
	public function setIp($ip) {
		$this->ip = $ip;
	}
	/**
	 * @return \DateTime $datetime
	 */
	public function getDateTime() {
		return $this->dateTime;
	}
	/**
	 * @param \DateTime $datetime
	 */
	public function setDatetime(\DateTime $dateTime) {
		$this->dateTime = $dateTime;
	}
}