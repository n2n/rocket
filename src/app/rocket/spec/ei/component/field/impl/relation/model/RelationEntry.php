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
namespace rocket\spec\ei\component\field\impl\relation\model;

use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\util\model\EiuFrame;

class RelationEntry {
	private $eiObject;
	private $eiMapping;

	private function __construct(EiObject $eiObject, EiMapping $eiMapping = null) {
		$this->eiObject = $eiObject;
		$this->eiMapping = $eiMapping;
	}

	public function isNew(): bool {
		return !$this->eiObject->getEiEntityObj()->isPersistent();
	}
	
	public function getId() {
		return $this->eiObject->getEiEntityObj()->getId();
	}
	
	public function getEiObject(): EiObject {
		return $this->eiObject;
	}
	
	public function hasEiMapping(): bool {
		return $this->eiMapping !== null;
	}

	public function getEiMapping() {
		return $this->eiMapping;
	}
	
	public function toEiMapping(EiuFrame $utils): EiMapping {
		if ($this->eiMapping !== null) {
			return $this->eiMapping;
		}
		
		return $utils->createEiMapping($this->eiObject);
	}
	
	public static function from(EiObject $eiObject): RelationEntry {
		return new RelationEntry($eiObject, null);
	}
	
	public static function fromM(EiMapping $eiMapping): RelationEntry {
		return new RelationEntry($eiMapping->getEiObject(), $eiMapping);
	}
}
