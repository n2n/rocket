<?php
namespace rocket\ei\manage\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\ui\DisplayItem;

class DisplayDefinition {
	private $displayItemType;
	private $defaultDisplayed;
	
	/**
	 * @param Lstr $labelLstr
	 * @param string $displayItemType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(string $displayItemType, bool $defaultDisplayed) {
		ArgUtils::valEnum($displayItemType, DisplayItem::getTypes());
		
		$this->displayItemType = $displayItemType;
		$this->defaultDisplayed = $defaultDisplayed;
	}
	
	/**
	 * @return string
	 */
	public function getDisplayItemType(): string {
		return $this->displayItemType;
	}
	
	/**
	 * @return bool
	 */
	public function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
}

