<?php
namespace rocket\spec\ei\manage\gui;

use rocket\spec\ei\manage\gui\ui\DisplayItem;
use n2n\reflection\ArgUtils;

class DisplayDefinition {
	private $label;
	private $groupType;
	private $defaultDisplayed;
	
	/**
	 * @param string $label
	 * @param string $groupType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(string $label, string $groupType, bool $defaultDisplayed, string $helpText = null) {
		$this->label = $label;
		ArgUtils::valEnum($groupType, DisplayItem::getTypes());
		$this->groupType = $groupType;
		$this->defaultDisplayed = $defaultDisplayed;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string
	 */
	public function getGroupType() {
		return $this->groupType;
	}
	
	/**
	 * @return bool
	 */
	public function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
}

