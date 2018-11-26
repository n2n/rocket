<?php
namespace rocket\impl\ei\component\prop\adapter\entry;

use n2n\l10n\Message;
use rocket\ei\manage\gui\EiFieldAbstraction;
use rocket\ei\manage\entry\ValidationResult;

class EiFieldWrapperCollection implements EiFieldAbstraction {
	private $eiFieldWrappers = array();
	
	public function __construct(array $eiFieldWrappers) {
		$this->eiFieldWrappers = $eiFieldWrappers;
	}
	
	public function isIgnored(): bool {
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			if (!$eiFieldWrapper->isIgnored()) return false;
		}
		
		return true;
	}
	
	public function setIgnored(bool $ignored) {
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			$eiFieldWrapper->setIgnored($ignored);
		}
	}
	
	public function getValidationResult(): ValidationResult {
		$validationResult = new ValidationResultCollection();
		
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			$validationResult->add($eiFieldWrapper->getValidationResult());
		}
		
		return $validationResult;
	}
}

class ValidationResultCollection implements ValidationResult {
	/**
	 * @var ValidationResult[]
	 */
	private $validationResults = [];
	
	public function add(ValidationResult $validationResult) {
		$this->validationResults[] = $validationResult;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\ValidationResult::processMessage()
	 */
	public function processMessage(bool $recursive): ?Message {
		if (!$recursive) return false;
		
		foreach ($this->validationResults as $validationResult) {
			if (null !== ($msg = $validationResult->processMessage($recursive))) {
				return $msg;
			}
		}
		
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\ValidationResult::isValid()
	 */
	public function isValid(bool $checkRecurisve = true): bool {
		if (!$recursive) return true;
		
		foreach ($this->validationResults as $validationResult) {
			if (!$validationResult->isValid($checkRecurisve)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\ValidationResult::getMessages()
	 */
	public function getMessages(): array {
		if (!$recursive) return [];
		
		$messages = [];
		
		foreach ($this->validationResults as $validationResult) {
			array_push($messages, ...$validationResult->getMessages());
		}
		
		return $messages;
	}

	
}