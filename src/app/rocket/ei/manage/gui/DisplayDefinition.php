<?php
namespace rocket\ei\manage\gui;

use n2n\l10n\Lstr;

class DisplayDefinition {
	private $displayItemType;
	private $defaultDisplayed;
	
	/**
	 * @param Lstr $labelLstr
	 * @param string $displayItemType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(bool $defaultDisplayed) {
		$this->defaultDisplayed = $defaultDisplayed;
	}
	
// 	/**
// 	 * @return Lstr
// 	 */
// 	public function getLabelLstr() {
// 		return $this->labelLstr;
// 	}
	
// 	/**
// 	 * @return string
// 	 */
// 	public function getDisplayItemType(): string {
// 		return $this->displayItemType;
// 	}
	
	/**
	 * @return bool
	 */
	public function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
}

