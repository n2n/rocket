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
namespace rocket\ei\manage\mapping;

use n2n\l10n\Message;

class FieldErrorInfo {
	private $errorMessages = array();
	private $subFieldErrorInfos = array();
	private $subMappingErrorInfos = array();

	public function __construct() {
	}

	public function clear() {
		$this->errorMessages = array();
	}

	public function isValid(bool $checkRecursive = true): bool {
		if (!empty($this->errorMessages)) return false;

		if (!$checkRecursive) return true;

		foreach ($this->subFieldErrorInfos as $subFieldErrorInfo) {
			if (!$subFieldErrorInfo->isValid(true)) return false;
		}
		
		foreach ($this->subMappingErrorInfos as $subMappingErrorInfo) {
			if (!$subMappingErrorInfo->isValid(true)) return false;
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
	
	public function addSubFieldErrorInfo(FieldErrorInfo $fieldErrorInfo) {
		$this->subFieldErrorInfos[] = $fieldErrorInfo;
	}

	public function addSubMappingErrorInfo(MappingErrorInfo $subMappingErrorInfo) {
		$this->subMappingErrorInfos[] = $subMappingErrorInfo;
	}

	public function getMessages() {
		$messages = $this->errorMessages;

		foreach ($this->subMappingErrorInfos as $subMappingErrorInfo) {
			$messages = array_merge($messages, $subMappingErrorInfo->getMessages());
		}

		return $messages;
	}
}
