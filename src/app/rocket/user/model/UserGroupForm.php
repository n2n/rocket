<?php
namespace rocket\user\model;

use n2n\dispatch\Dispatchable;
use rocket\script\core\ScriptManager;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use rocket\user\bo\UserGroup;
use rocket\user\bo\UserScriptGrant;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\val\ValNotEmpty;
use n2n\core\N2nContext;
use n2n\dispatch\option\impl\OptionForm;
use n2n\util\Attributes;
use rocket\script\entity\filter\FilterForm;
use n2n\dispatch\val\ValArrayKeys;

class UserGroupForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->annotateClass(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('name', 'menuItemRestrictionEnabled', 'accessableMenuItemIds')));
		$as->annotateMethod('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $scriptManager;
	private $userGroup;

	private $menuItemRestrictionEnabled = false;
	private $accessableMenuItemIds = array();
	
	public function __construct(UserGroup $userGroup, ScriptManager $scriptManager, N2nContext $n2nContext) {
		$this->scriptManager = $scriptManager;		
		$this->userGroup = $userGroup;
		
		$this->menuItemRestrictionEnabled = $userGroup->isMenuItemAccessRestricted();
		if ($this->menuItemRestrictionEnabled) {
			$ids = $userGroup->getAccessableMenuItemIds();
			$this->accessableMenuItemIds = array_combine($ids, $ids);
		}
	}
	
	public function getUserGroup() {
		return $this->userGroup;
	}
	
	public function getAccessableMenuItemIdOptions() {
		$menuItemIdOptions = array();
		foreach ($this->scriptManager->getMenuGroups() as $menuGroup) {
			foreach ($menuGroup->getMenuItems() as $menuItem) {
				$menuItemIdOptions[$menuItem->getId()] = $menuGroup->getLabel() . ' > ' . $menuItem->getLabel(); 
			}
		}
		return $menuItemIdOptions;
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('name', new ValNotEmpty());
		$bc->val('accessableMenuItemIds', new ValArrayKeys(array_keys($this->getAccessableMenuItemIdOptions())));
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
	
	public function getAccessableMenuItemIds() {
		return $this->accessableMenuItemIds;
	}
	
	public function setAccessableMenuItemIds(array $accessableMenuItemIds) {
		$this->accessableMenuItemIds = $accessableMenuItemIds;
	}
		
	public function save() {
		if (!$this->menuItemRestrictionEnabled) {
			$this->userGroup->setAccessableMenuItemIds(null);
		} else {
			$this->userGroup->setAccessableMenuItemIds(array_keys($this->accessableMenuItemIds));
		}
	}
}