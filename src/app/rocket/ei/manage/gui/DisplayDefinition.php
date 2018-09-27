<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\gui\ui\DisplayItem;
use n2n\reflection\ArgUtils;
use n2n\l10n\Lstr;

class DisplayDefinition {
	private $labelLstr;
	private $displayItemType;
	private $defaultDisplayed;
	
	/**
	 * @param Lstr $labelLstr
	 * @param string $displayItemType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(Lstr $labelLstr, string $displayItemType, bool $defaultDisplayed, string $helpText = null) {
		$this->labelLstr = $labelLstr;
		ArgUtils::valEnum($displayItemType, DisplayItem::getTypes());
		$this->displayItemType = $displayItemType;
		$this->defaultDisplayed = $defaultDisplayed;
	}
	
	/**
	 * @return Lstr
	 */
	public function getLabelLstr() {
		return $this->labelLstr;
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

