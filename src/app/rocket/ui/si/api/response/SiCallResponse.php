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
namespace rocket\ui\si\api\response;

use n2n\l10n\Message;
use n2n\util\type\ArgUtils;
use n2n\l10n\N2nLocale;
use rocket\ui\si\content\SiEntryIdentifier;
use rocket\si\input\SiInputError;

class SiCallResponse implements \JsonSerializable {
	const DIRECTIVE_REDIRECT_BACK = 'redirectBack';
	const DIRECTIVE_REDIRECT = 'redirect';
	
	const EVENT_TYPE_CHANGED = 'changed';
	const EVENT_TYPE_REMOVED = 'removed';
	const EVENT_TYPE_ADDED = 'added';
	
	private $directive;
	private $navPoint;
	/**
	 * @var array
	 */
	private $messageArr = [];
	/**
	 * @var \rocket\ui\si\control\SiButton
	 */
	private $newButton;
	/**
	 * @var array
	 */
	private $highlightMap = [];
	/**
	 * @var array
	 */
	private $eventMap = [];
	
	/**
	 * @param string|null $directive
	 * @return \rocket\si\control\SiCallResponse
	 */
	function setDirective(?string $directive) {
		ArgUtils::valEnum($directive, [self::DIRECTIVE_REDIRECT_BACK, self::DIRECTIVE_REDIRECT], null, true);
		$this->directive = $directive;
		return $this;
	}
	
	/**
	 * @param \rocket\ui\si\control\SiNavPoint|null $navPoint
	 * @return \rocket\si\control\SiCallResponse
	 */
	function setNavPoint(?\rocket\ui\si\control\SiNavPoint $navPoint) {
		$this->navPoint = $navPoint;
		return $this;
	}
	
	/**
	 * @param string $category
	 * @param string $id
	 * @return \rocket\si\control\SiCallResponse
	 */
	function addHighlight(string $category, string $id) {
		if (!isset($this->highlightMap[$category])) {
			$this->highlightMap[$category] = array('ids' => []);
		}
		
		$this->highlightMap[$category]['ids'][$id] = true;
		return $this;
	}
	
	/**
	 * @param string $category
	 * @param string $id
	 * @param string $modType
	 * @return \rocket\si\control\SiCallResponse
	 */
	function addEvent(string $typeId, string $entryId, string $modType) {
		ArgUtils::valEnum($modType, [self::EVENT_TYPE_ADDED, self::EVENT_TYPE_CHANGED, self::EVENT_TYPE_REMOVED]);

		if (!isset($this->eventMap[$typeId])) {
			$this->eventMap[$typeId] = array('ids' => []);
		}
		
		$this->eventMap[$typeId]['ids'][$entryId] = $modType;
		return $this;
	}
	
	function setNewButton(?\rocket\ui\si\control\SiButton $newButton) {
		$this->newButton = $newButton;
		return $this;
	}
	
	/**
	 * @param Message $message
	 * @param N2nLocale $n2nLocale
	 * @return \rocket\si\control\SiCallResponse
	 */
	function addMessage(Message $message, N2nLocale $n2nLocale) {
		$severity = null;
		switch ($message->getSeverity()) {
			case Message::SEVERITY_INFO:
				$severity = 'info';
				break;
			case Message::SEVERITY_ERROR:
				$severity = 'error';
				break;
			case Message::SEVERITY_SUCCESS:
				$severity = 'success';
				break;
			case Message::SEVERITY_WARN:
				$severity = 'warn';
				break;
		}
		
		$this->messageArr[] = [
			'text' => $message->t($n2nLocale),
			'severity' => $severity
		];
		
		return $this;
	}
	
	function jsonSerialize(): mixed {
		return [
			'directive' => $this->directive,
			'navPoint' => $this->navPoint,
			'messages' => $this->messageArr,
			'newButton' => $this->newButton,
			'highlightMap' => $this->highlightMap,
			'eventMap' => $this->eventMap
		];
	}
}