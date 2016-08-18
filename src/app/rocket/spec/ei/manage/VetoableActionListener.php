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
namespace rocket\spec\ei\manage;

use n2n\core\container\N2nContext;
use n2n\l10n\Message;

interface VetoableActionListener {
	
	public function onRemove(LiveEntry $liveEntry, VetoableRemoveAction $vetoableAction,
			N2nContext $n2nContext);
}

class VetoableRemoveAction {
	private $liveEntries = array();
	private $vetoReasonMessages = array();
	private $whenApprovedClosures = array();
	
	public function __construct(array $liveEntries) {
		foreach ($liveEntries as $liveEntry) {
			$this->liveEntries[spl_object_hash($liveEntry->getEntityObj())] = $liveEntry;
		}
	}
	
	public function containsEntityObj($entityObj): bool {
		return isset($this->liveEntries[spl_object_hash($entityObj)]);
	}
	
	public function registerVeto(Message $reasonMessage) {
		$this->vetoReasonMessages[] = $reasonMessage;
	}
	
	public function hasVetos(): bool {
		return !empty($this->vetoReasonMessages);
	}
	
	public function getReasonMessages() {
		return $this->vetoReasonMessages;
	}
	
	public function executeWhenApproved(\Closure $whenApprovedClosure) {
		$this->whenApprovedClosures[] = $whenApprovedClosure;
	}
	
	public function approve(): bool {
		if ($this->hasVetos()) return false;
	
		foreach ($this->whenApprovedClosures as $whenApprovedClosure) {
			$whenApprovedClosure();
		}
		
		return true;
	}
}
