<?php
namespace rocket\script\core;

class MenuGroup {
	private $label;
	private $menuItems;
	
	public function __construct($label, array $menuItems) {
		$this->label = $label;
		$this->menuItems = $menuItems;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getMenuItems() {
		return $this->menuItems;
	}
	
	public function putMenuItem($id, $label) {
		$this->menuItems[$id] = $label;
	}
	
	public function containsMenuItemId($id) {
		return isset($this->menuItems[$id]);
	}
	
	public function removeMenuPointById($id) {
		unset($this->menuItems[$id]);
	}
	
	public function getMenuItemById($id) {
		if (isset($this->menuItems[$id])) {
			return $this->menuItems[$id];
		}
		
		throw new UnknownMenuItemException($id);
	}
}
