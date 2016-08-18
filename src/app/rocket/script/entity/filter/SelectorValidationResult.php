<?php

namespace rocket\script\entity\filter;

use n2n\core\Message;

class SelectorValidationResult {
	private $messages = array();
	
	public function __construct() {
		
	}
	
	public function addError($id, Message $message) {
		$this->messages[] = $message;
	}
	
	public function hasFailed() {
		return 0 < sizeof($this->messages);
	}
	
	public function isValid() {
		return empty($this->messages);
	}
	
	public function getMessages() {
		return $this->messages;
	}
}