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
namespace rocket\user\bo;

use n2n\util\type\attrs\DataSet;
use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\util\StringUtils;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToOne;

class CustomGrant extends ObjectAdapter implements Grant {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_custom_grant'));
		$ai->p('rocketUserGroup', new AnnoManyToOne(RocketUserGroup::getClass()));
	}

	private $id;
	private $customSpecId;
	private $rocketUserGroup;
	private $full;
	private $accessJson = '[]';
	
	public function __construct() {
		$this->privilegesGrants = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
		
	public function getRocketUserGroup() {
		return $this->rocketUserGroup;
	}
	
	public function setRocketUserGroup(RocketUserGroup $userGroup) {
		$this->rocketUserGroup = $userGroup;
	}
	
	public function getCustomTypeId() {
		return $this->customSpecId;
	}
	
	public function setCustomTypeId($customSpecId) {
		$this->customSpecId = $customSpecId;
	}
	
	public function isFull(): bool {
		return $this->full;
	}
	
	public function setFull(bool $full) {
		$this->full = $full;
	}
	
	public function readAccessDataSet() {
		return new DataSet(StringUtils::jsonDecode($this->accessJson, true));
	}
	
	public function writeAccessDataSet(DataSet $accessDataSet) {
		$this->accessJson = StringUtils::jsonEncode($accessDataSet->toArray());
	}
	
	public function getAccessDataSet() {
		return $this->readAccessDataSet();
	}
}
