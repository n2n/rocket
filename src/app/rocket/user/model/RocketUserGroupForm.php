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

use n2n\web\dispatch\Dispatchable;
use rocket\spec\SpecManager;
use n2n\reflection\annotation\AnnoInit;
use rocket\user\bo\RocketUserGroup;
use n2n\impl\web\dispatch\map\val\ValNotEmpty;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\map\val\ValArrayKeys;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\core\model\LayoutManager;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\l10n\DynamicTextCollection;

class RocketUserGroupForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('name', 'menuItemRestrictionEnabled', 'accessibleMenuItemIds'));
	}
	
	private $specManager;
	private $layoutManager;
	private $userGroup;

	private $menuItemRestrictionEnabled = false;
	private $accessibleMenuItemIds = array();
	
	public function __construct(RocketUserGroup $userGroup, LayoutManager $layoutManager, SpecManager $specManager, N2nContext $n2nContext) {
		$this->specManager = $specManager;
		$this->layoutManager = $layoutManager;
		$this->userGroup = $userGroup;
		
		$this->menuItemRestrictionEnabled = $userGroup->isMenuItemAccessRestricted();
		if ($this->menuItemRestrictionEnabled) {
			$ids = $userGroup->getAccessibleMenuItemIds();
			$this->accessibleMenuItemIds = array_combine($ids, $ids);
		}
	}
	
	public function getRocketUserGroup() {
		return $this->userGroup;
	}
	
	public function getAccessibleMenuItemIdOptions() {
		$menuItemIdOptions = array();
		foreach ($this->layoutManager->getMenuGroups() as $menuGroup) {
			foreach ($menuGroup->getMenuItems() as $menuItem) {
				$menuItemIdOptions[$menuItem->getId()] = $menuGroup->getLabel() . ' > ' . $menuItem->getLabel(); 
			}
		}
		return $menuItemIdOptions;
	}
	
	private function _mapping(MappingDefinition $md, DynamicTextCollection $dtc) {
		$md->getMappingResult()->setLabel('name', $dtc->translate('common_name_label'));
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val('name', new ValNotEmpty());
		$bd->val('accessibleMenuItemIds', new ValArrayKeys(array_keys($this->getAccessibleMenuItemIdOptions())));
	}
	
	public function isNew() {
		return null === $this->userGroup->getId();
	}
	
	public function getName() {
		return $this->userGroup->getName();
	}
	
	public function setName($name) {
		$this->userGroup->setName($name);
	}
	
	public function isMenuItemRestrictionEnabled() {
		return $this->menuItemRestrictionEnabled;
	}
	
	public function setMenuItemRestrictionEnabled($menuItemRestrictionEnabled) {
		$this->menuItemRestrictionEnabled = $menuItemRestrictionEnabled;
	}
	
	public function getaccessibleMenuItemIds() {
		return $this->accessibleMenuItemIds;
	}
	
	public function setaccessibleMenuItemIds(array $accessibleMenuItemIds) {
		$this->accessibleMenuItemIds = $accessibleMenuItemIds;
	}
		
	public function save() {
		if (!$this->menuItemRestrictionEnabled) {
			$this->userGroup->setAccessibleMenuItemIds(null);
		} else {
			$this->userGroup->setAccessibleMenuItemIds(array_keys($this->accessibleMenuItemIds));
		}
	}
}
