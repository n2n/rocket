<?php
namespace rocket\spec\ei\manage\veto;

class RemoveAttempt {
	private $reasonMessages;
	
	public function __construct(array $reasonMessages) {
		$this->reasonMessages = $reasonMessages;
	}
	
	/**
	 * @return boolean
	 */
	public function isSuccessful() {
		return !empty($this->reasonMessages);
	}
	
	public function getReasonMessages() {
		return $this->reasonMessages;
	}
}