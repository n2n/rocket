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
namespace rocket\user\model;

use rocket\user\bo\RocketUserGroup;
use rocket\spec\ei\EiType;
use rocket\user\bo\EiGrant;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\config\CustomSpec;
use rocket\user\bo\CustomGrant;
use rocket\user\bo\Grant;

class GroupGrantsViewModel {
	private $userGroup;
	private $eiTypeItems = array();
	private $customItems = array();
		
	public function __construct(RocketUserGroup $userGroup, array $eiTypes, array $customSpecs) {
		$this->userGroup = $userGroup;
				
		foreach ($eiTypes as $eiType) {
			if ($eiType->hasSuperEiType()) continue; 
			
			$this->applyEiTypeItems($eiType, 0);
		}
		
		foreach ($customSpecs as $customSpec) {
			$this->customItems[$customSpec->getId()] = new CustomSpecItem($customSpec, 
					$this->findCustomGrant($customSpec));
		}
	}
	
	private function findEiGrant(EiType $eiType, EiMask $eiMask = null) {
		$eiTypeId = $eiType->getId();
		$eiMaskId = null;
		
		if ($eiMask !== null) {
			$eiMaskId = $eiMask->getExtension()->getId();
		}
		
		foreach ($this->userGroup->getEiGrants() as $eiGrant) {
			if ($eiTypeId === $eiGrant->getEiTypeId() && $eiMaskId === $eiGrant->getEiMaskId()) {
				return $eiGrant;
			}
		}
		
		return null;
	}
	
	private function findCustomGrant(CustomSpec $customSpec) {
		$customSpecId = $customSpec->getId();
	
		foreach ($this->userGroup->getCustomGrants() as $customGrant) {
			if ($customSpecId === $customGrant->getCustomSpecId()) {
				return $customGrant;
			}
		}
	
		return null;
	}
	
	private function applyEiTypeItems(EiType $eiType, int $level) {
		$this->eiTypeItems[$eiType->getId()] = $eiTypeItem = new EiTypeItem($level, $eiType, $this->findEiGrant($eiType));
		
		foreach ($eiType->getEiMaskCollection() as $eiMask) {
			$eiTypeItem->addEiMaskItem(new EiMaskItem($eiMask, $this->findEiGrant($eiType, $eiMask)));
		}
		
		$level++;
		foreach ($eiType->getSubEiTypes() as $subEiType) {
			$this->applyEiTypeItems($subEiType, $level);
		}
	}
	
	public function getGroupId() {
		return $this->userGroup->getId();
	}
	
	public function getRocketUserGroup() {
		return $this->userGroup;
	}
	
	/**
	 * @return EiTypeItem[]
	 */
	public function getEiTypeItems() {
		return $this->eiTypeItems;
	}
	
	/**
	 * @return CustomSpecItem[]
	 */
	public function getCustomItems() {
		return $this->customItems;
	}
}

class Item {
	private $grant;
	
	public function __construct(Grant $grant = null) {
		$this->grant = $grant;
	}
	
	public function isAccessible(): bool {
		return $this->grant !== null;
	}
	
	public function isFullyAccessible(): bool {
		return $this->grant !== null && $this->grant->isFull();
	}
}

class EiTypeItem extends Item {
	private $level;
	private $eiType;
	private $eiMaskItems = array();
	
	public function __construct(int $level, EiType $eiType, EiGrant $eiGrant = null) {
		parent::__construct($eiGrant);
		$this->level = $level;
		$this->eiType = $eiType;	
	}
	
	public function getLevel(): int {
		return $this->level;
	}
	
	public function getEiTypeId(): string {
		return $this->eiType->getId();
	}
	
	public function getLabel(): string {
		if (null !== ($label = $this->eiType->getEiMask()->getLabel())) {
			return $label;
		}
		
		return $this->eiType->getEiMaskCollection()->getOrCreateDefault()->getLabel();
	}
	
	public function getEiMaskItems(): array {
		return $this->eiMaskItems;
	}
	
	public function addEiMaskItem(EiMaskItem $eiMaskItem) {
		$this->eiMaskItems[] = $eiMaskItem;
	}
}

class EiMaskItem extends Item {
	private $eiMask;
	
	public function __construct(EiMask $eiMask, EiGrant $eiGrant = null) {
		parent::__construct($eiGrant);
		$this->eiMask = $eiMask;
	}
	
	public function getEiMaskId(): string {
		return $this->eiMask->getExtension()->getId();
	}
	
	public function getLabel(): string {
		return $this->eiMask->getLabelLstr();
	}
}

class CustomSpecItem extends Item {
	private $customSpec;
	
	public function __construct(CustomSpec $customSpec, CustomGrant $customGrant = null) {
		parent::__construct($customGrant);
		$this->customSpec = $customSpec;
	}
	
	public function getCustomSpecId() {
		return $this->customSpec->getId();
	}
	
	public function getLabel(): string {
		return $this->customSpec->getLabel();
	}
}
