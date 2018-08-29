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

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\CascadeType;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use rocket\spec\TypePath;

class EiGrant extends ObjectAdapter implements Grant {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_ei_grant'));
		$ai->p('rocketUserGroup', new AnnoManyToOne(RocketUserGroup::getClass()));
		$ai->p('eiPrivilegeGrants', new AnnoOneToMany(EiPrivilegeGrant::getClass(), 'eiGrant', CascadeType::ALL));
	}

	private $id;
	private $eiTypePath;
	private $rocketUserGroup;
	private $full = false;
	private $eiPrivilegeGrants;
	
	public function __construct() {
		$this->eiPrivilegeGrants = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getEiTypePath() {
		return TypePath::create($this->eiTypePath);
	}
	
	public function setEiTypePath(TypePath $eiTypePath) {
		$this->eiTypePath = $eiTypePath;
	}
		
	public function getRocketUserGroup() {
		return $this->rocketUserGroup;
	}
	
	public function setRocketUserGroup(RocketUserGroup $userGroup) {
		$this->rocketUserGroup = $userGroup;
	}
	
	public function isFull(): bool {
		return $this->full;
	}
	
	public function setFull(bool $full) {
		$this->full = $full;
	}
	
	public function getAccessAttributes() {
		return $this->readAccessAttributes();
	}
	
	public function getEiPrivilegeGrants() {
		return $this->eiPrivilegeGrants;
	}
	
	public function setEiPrivilegeGrants(\ArrayObject $privilegeGrants) {
		$this->eiPrivilegeGrants = $privilegeGrants;
	}
}
