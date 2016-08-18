<?php

namespace rocket\script\entity\field\impl\ci\model;

class PanelConfig {
	private $name;
	private $label;
	private $allowedContentItemIds;
	
	public function __construct($name, $label, array $allowedContentItemIds) {
		$this->name = $name; 
		$this->label = $label;
		$this->allowedContentItemIds = $allowedContentItemIds;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function isRestricted() {
		return (boolean)sizeof($this->allowedContentItemIds);
	}
	
	public function getAllowedContentItemIds() {
		return $this->allowedContentItemIds;
	}
	
	public function setAllowedContentItemIds($allowedContentItemIds) {
		$this->allowedContentItemIds = $allowedContentItemIds;
	}
}