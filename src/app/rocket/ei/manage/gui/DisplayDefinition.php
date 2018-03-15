<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\reflection\ArgUtils;

class DisplayDefinition {
	private $label;
	private $displayItemType;
	private $defaultDisplayed;
	
	/**
	 * @param string $label
	 * @param string $displayItemType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(string $label, string $displayItemType, bool $defaultDisplayed, string $helpText = null) {
		$this->label = $label;
		ArgUtils::valEnum($displayItemType, DisplayItem::getTypes());
		$this->displayItemType = $displayItemType;
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
	public function getDisplayItemType(): ?string {
		return $this->displayItemType;
	}
	
	/**
	 * @return bool
	 */
	public function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
}

