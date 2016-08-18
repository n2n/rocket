<?php
namespace rocket\core\model;

use rocket\user\model\LoginContext;
use rocket\script\core\ManageState;
use rocket\script\core\MenuItem;
use n2n\model\Usable;

class TemplateModel implements Usable {
	
	const TOP_PANEL_VIEW_ID = 'paneltop_view';
	const BOTTOM_PANEL_VIEW_ID = 'panel_bottom_view';
	const RIGHT_PANEL_VIEW_ID = 'panel_right_view';
	const LEFT_PANEL_VIEW_ID = 'panel_left_view';
	
	private $currentUser;
	private $selectedMenuItemId;
	private $breadcrumbs;
	private $activeBreadcrumb;
	private $navArray = array();
	
	private function _init(LoginContext $loginContext, ManageState $manageState, Rocket $rocket, RocketState $rocketState) {
		$this->currentUser = $loginContext->getCurrentUser();
				
		$this->selectedMenuItemId = null;
		if (null !== ($selectedMenuItem = $manageState->getSelectedMenuItem())) {
			$this->selectedMenuItemId = $selectedMenuItem->getId();
		}
		
		$this->breadcrumbs = $rocketState->getBreadcrumbs()->getArrayCopy();
		if (sizeof($this->breadcrumbs)) {
			$this->activeBreadcrumb = array_pop($this->breadcrumbs);
		}
		
		$this->initNavArray($rocket);
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
	
	private function initNavArray(Rocket $rocket) {
		$accessableMenuItemIds = $this->getAccesableMenuItemIds();
		$this->navArray = array();
		foreach ($rocket->getScriptManager()->getMenuGroups() as $menuGroup) {
			$menuItems = $menuGroup->getMenuItems();
			if ($accessableMenuItemIds !== null) {
				foreach ($menuItems as $key => $menuItem) {
					if (in_array($menuItem->getId(), $accessableMenuItemIds)) continue;
					unset($menuItems[$key]);
				}
			}
				
			$this->navArray[] = array('label' => $menuGroup->getLabel(),
					'open' => $menuGroup->containsMenuItemId($this->selectedMenuItemId),
					'menuItems' => $menuItems);
		}
	}
	
	private function getAccesableMenuItemIds() {
		if ($this->currentUser->isSuperAdmin()) return null;
		
		$accessableMenuItemIds = null;
		if (!$this->currentUser->isAdmin()) $accessableMenuItemIds = array();
		
		foreach ($this->currentUser->getUserGroups() as $userGroup) {
			if (!$userGroup->isMenuItemAccessRestricted()) {
				return null;
			}
			
			$accessableMenuItemIds = array_merge((array) $accessableMenuItemIds, 
					$userGroup->getAccessableMenuItemIds());
		}
		
		return $accessableMenuItemIds;
	}
	
	public function getNavArray() {
		return $this->navArray;
	}
	
	public function isMenuItemSelected(MenuItem $menuItem) {
		return $this->selectedMenuItemId === $menuItem->getId();
	}
}