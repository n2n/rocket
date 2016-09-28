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
namespace rocket\spec\ei\manage\draft;

use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\LiveEntry;

class Draft {
	const FLAG_PUBLISHED = 'published';
	const FLAG_RECOVERY = 'recovery';
	
	private $id;
	private $liveEntry;
	private $lastMod;
	private $flag;
	private $listed;
	private $userId;
	private $draftValueMap = array();
	
	public function __construct(int $id = null, LiveEntry $liveEntry, \DateTime $lastMod, 
			int $userId = null, DraftValueMap $draftValueMap) {
		$this->id = $id;
		$this->liveEntry = $liveEntry;
		$this->lastMod = $lastMod;
		$this->userId = $userId;
		$this->draftValueMap = $draftValueMap;
	}
	
	public function isNew() {
		return $this->id === null;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getLiveEntry(): LiveEntry {
		return $this->liveEntry;
	}
	
	public function getLastMod(): \DateTime {
		return $this->lastMod;
	}
	
	public function setLastMod(\DateTime $lastMod) {
		$this->lastMod = $lastMod;
	}
	
	public function isPublished(): bool {
		return $this->flag == self::FLAG_PUBLISHED;
	}
	
	public function isRevorery(): bool {
		return $this->flag == self::FLAG_RECOVERY;
	}
	
	public function setFlag(string $flag = null) {
		ArgUtils::valEnum($flag, self::getFlags(), null, true);
		$this->flag = $flag;
	}
	
	public function getFlag() {
		return $this->flag;
	}
	
	/**
	 * @return string[]
	 */
	public static function getFlags() {
		return array(self::FLAG_PUBLISHED);
	}
	
	public function getUserId(): int {
		return $this->userId;
	}
	
	public function setUserId(int $userId = null) {
		$this->userId = $userId;
	}
	
	public function getDraftValueMap() {
		return $this->draftValueMap;
	}
	
	public function setDraftedEntityObj($draftedEntityObj) {
		ArgUtils::valObject($draftedEntityObj, false, 'draftedEntityObj');
		$this->draftedEntityObj = $draftedEntityObj;
	}
	
	public function getDraftedEntityObj() {
		return $this->draftedEntityObj;
	}
	
	public function equals($obj) {
		if (!($obj instanceof Draft)) return false;
		
		return $this->getId() == $obj->getId() && $this->getLiveEntry()->equals($obj->getLiveEntry());
	}
}
