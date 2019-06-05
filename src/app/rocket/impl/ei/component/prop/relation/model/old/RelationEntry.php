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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\frame\EiuFrame;

class RelationEntry {
	private $eiObject;
	private $eiEntry;

	private function __construct(EiObject $eiObject, EiEntry $eiEntry = null) {
		$this->eiObject = $eiObject;
		$this->eiEntry = $eiEntry;
	}

	public function isNew(): bool {
		return !$this->eiObject->getEiEntityObj()->isPersistent();
	}
	
	public function getId() {
		return $this->eiObject->getEiEntityObj()->getId();
	}
	
	public function getPid() {
		return $this->eiObject->getEiEntityObj()->getPid();
	}
	
	public function getEiObject(): EiObject {
		return $this->eiObject;
	}
	
	public function hasEiEntry(): bool {
		return $this->eiEntry !== null;
	}

	public function getEiEntry() {
		return $this->eiEntry;
	}
	
	public function toEiEntry(EiuFrame $utils): EiEntry {
		if ($this->eiEntry !== null) {
			return $this->eiEntry;
		}
		
		return $this->eiEntry = $utils->entry($this->eiObject)->getEiEntry();
	}
	
	public static function from(EiObject $eiObject): RelationEntry {
		return new RelationEntry($eiObject, null);
	}
	
	public static function fromM(EiEntry $eiEntry): RelationEntry {
		return new RelationEntry($eiEntry->getEiObject(), $eiEntry);
	}
}
