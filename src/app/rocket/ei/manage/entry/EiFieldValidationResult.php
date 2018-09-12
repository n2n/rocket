<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\entry;

use n2n\l10n\Message;
use rocket\ei\EiPropPath;

class EiFieldValidationResult {
	private $eiPropPath;
	private $errorMessages = array();
	/**
	 * @var EiFieldValidationResult[]
	 */
	private $subEiFieldValidationResults = array();
	/**
	 * @var EiEntryValidationResult[]
	 */
	private $subEiEntryValidationResults = array();

	public function __construct(EiPropPath $eiPropPath) {
		$this->eiPropPath = $eiPropPath;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	function getEiPropPath() {
		return $this->eiPropPath;
	}

	/**
	 * 
	 */
	public function clear(bool $clearRecursive = true) {
		$this->errorMessages = array();
		
		if (!$clearRecursive) return;
		
		$this->clearSubOnly();
	}
	
	public function clearSubOnly() {
		$this->subEiEntryValidationResults = array();
		$this->subEiFieldValidationResults = array();
	}

	/**
	 * @param bool $checkRecursive
	 * @return bool
	 */
	public function isValid(bool $checkRecursive = true): bool {
		if (!empty($this->errorMessages)) return false;

		if (!$checkRecursive) return true;

		foreach ($this->subEiFieldValidationResults as $subEiFieldValidationResult) {
			if (!$subEiFieldValidationResult->isValid(true)) return false;
		}
		
		foreach ($this->subEiEntryValidationResults as $subEiEntryValidationResult) {
			if (!$subEiEntryValidationResult->isValid(true)) return false;
		}

		return true;
	}

	public function addError(Message $message) {
		$this->errorMessages[] = $message;
	}

	// 	public function getErrorMessages(): array {
	// 		return $this->errorMessages;
	// 	}

	public function processMessage(bool $checkrecursive = true) {
		foreach ($this->errorMessages as $errorMessage) {
			if ($errorMessage->isProcessed()) continue;
			
			$errorMessage->setProcessed(true);
			return $errorMessage;
		}
		
		return null;
	}
	
	public function addSubEiFieldValidationResult(EiFieldValidationResult $subValidationResult) {
		$this->subEiFieldValidationResults[] = $subValidationResult;
	}

	public function addSubEiEntryValidationResult(EiEntryValidationResult $subValidationResult) {
		$this->subEiEntryValidationResults[] = $subValidationResult;
	}

	public function getMessages(bool $recursive = true) {
		$messages = $this->errorMessages;
		
		if ($recursive) {
			foreach ($this->subEiEntryValidationResults as $subValidationResult) {
				$messages = array_merge($messages, $subValidationResult->getMessages());
			}
			
			foreach ($this->subEiFieldValidationResults as $subValidationResult) {
				$messages = array_merge($messages, $subValidationResult->getMessages());
			}
		}

		return $messages;
	}
}
