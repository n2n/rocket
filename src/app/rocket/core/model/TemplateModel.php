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
namespace rocket\core\model;

use rocket\user\model\LoginContext;
use n2n\context\Lookupable;
use n2n\core\container\N2nContext;

class TemplateModel implements Lookupable {
	private $currentUser;
	private $activeMenuItemId;
	private $breadcrumbs;
	private $activeBreadcrumb;
	private $navArray = array();
	
	private function _init(LoginContext $loginContext, Rocket $rocket, RocketState $rocketState, 
			N2nContext $n2nContext) {
		$this->currentUser = $loginContext->getCurrentUser();
				
		$this->activeMenuItemId = null;
		if (null !== ($activeMenuItem = $rocketState->getActiveMenuItem())) {
			$this->activeMenuItemId = $activeMenuItem->getId();
		}
		
		$this->breadcrumbs = $rocketState->getBreadcrumbs()->getArrayCopy();
		if (sizeof($this->breadcrumbs)) {
			$this->activeBreadcrumb = array_pop($this->breadcrumbs);
		}
		
		$this->initNavArray($rocket, $n2nContext);
	}
	
	public function getCurrentUser() {
		return $this->currentUser;
	}
	
	public function getBreadcrumbs() {
		return $this->breadcrumbs;
	}
	
	public function getActiveBreadcrumb() {
		return $this->activeBreadcrumb;
	}
	
	private function initNavArray(Rocket $rocket, N2nContext $n2nContext) {
		$accessibleMenuItemIds = $this->getAccesableMenuItemIds();
		$this->navArray = array();
		
		foreach ($rocket->getLayoutManager()->getMenuGroups() as $menuGroup) {
			$menuItems = $menuGroup->getMenuItems();
			
			foreach ($menuItems as $key => $menuItem) {
				if (($accessibleMenuItemIds !== null && !in_array($menuItem->getId(), $accessibleMenuItemIds))
						|| !$menuItem->isAccessible($n2nContext)) {
					unset($menuItems[$key]);
				}
			}
			
			
			if (empty($menuItems)) continue;
			
			$this->navArray[] = array('label' => $menuGroup->getLabel(),
					'open' => $menuGroup->containsMenuItemId($this->activeMenuItemId),
					'menuItems' => $menuItems);
		}
	}
	
	private function getAccesableMenuItemIds() {
		if ($this->currentUser->isSuperAdmin()) return null;
		
		$accessibleMenuItemIds = null;
		if (!$this->currentUser->isAdmin()) $accessibleMenuItemIds = array();
		
		foreach ($this->currentUser->getRocketUserGroups() as $userGroup) {
			if (!$userGroup->isMenuItemAccessRestricted()) {
				return null;
			}
			
			$accessibleMenuItemIds = array_merge((array) $accessibleMenuItemIds, 
					$userGroup->getaccessibleMenuItemIds());
		}
		
		return $accessibleMenuItemIds;
	}
	
	public function getNavArray() {
		return $this->navArray;
	}
	
	public function isMenuItemActive(MenuItem $menuItem) {
		return $this->activeMenuItemId === $menuItem->getId();
	}
}
