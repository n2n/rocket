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
use n2n\util\StringUtils;
use n2n\reflection\ArgUtils;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToMany;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\annotation\AnnoTransient;

class RocketUserGroup extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_user_group'));
		$ai->p('rocketUsers', new AnnoManyToMany(RocketUser::getClass(), 'rocketUserGroups'));
		$ai->p('eiGrants', new AnnoOneToMany(EiGrant::getClass(), 'rocketUserGroup', CascadeType::ALL));
		$ai->p('customGrants', new AnnoOneToMany(CustomGrant::getClass(), 'rocketUserGroup', CascadeType::ALL));
		$ai->p('accessibleMenuItemIds', new AnnoTransient());
	}
	
	private $id;
	private $name;
	private $rocketUsers;
	private $navJson = null;
	private $eiGrants;
	private $customGrants;
	
	public function __construct() {
		$this->rocketUsers = new \ArrayObject();
		$this->eiGrants = new \ArrayObject();
		$this->customGrants = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getRocketUsers() {
		return $this->rocketUsers;
	}
	
	public function setRocketUsers(\ArrayObject $users) {
		$this->rocketUsers = $users;
	}
	
	public function isMenuItemAccessRestricted() {
		return $this->navJson !== null;
	}
	
	private $accessibleMenuItemIds = null;
	/**
	 * @return array if null is returned all MenuItems are accessible.
	 */
	public function getAccessibleMenuItemIds() {
		if ($this->navJson === null)  {
			throw new IllegalStateException();
		}
		
		if ($this->accessibleMenuItemIds !== null) {
			return $this->accessibleMenuItemIds;
		}
		
		return $this->accessibleMenuItemIds = StringUtils::jsonDecode($this->navJson, true);
	}
	
	public function setAccessibleMenuItemIds(array $menuItemIds = null) {
		if ($menuItemIds === null) {
			$this->navJson = null;
			$this->accessibleMenuItemIds = null;
			return;
		}
		
		ArgUtils::valArray($menuItemIds, 'string');
		$this->accessibleMenuItemIds = $menuItemIds;
		$this->navJson = StringUtils::jsonEncode($menuItemIds);
	}
	
	public function containsAccessibleMenuItemId(string $id): bool {
		return in_array($id, $this->getAccessibleMenuItemIds(), true);
	}
	
	/**
	 * @return EiGrant[]
	 */
	public function getEiGrants() {
		return $this->eiGrants;
	}
	
	public function setEiGrants(\ArrayObject $eiGrants) {
		$this->eiGrants = $eiGrants;
	}
	
	public function getCustomGrants() {
		return $this->customGrants;
	}
	
	public function setCustomGrants(\ArrayObject $customGrants) {
		$this->customGrants = $customGrants;
	}
}
