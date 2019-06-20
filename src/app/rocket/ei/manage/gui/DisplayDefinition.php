<?php
namespace rocket\ei\manage\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\si\structure\SiStructureType;

class DisplayDefinition {
	private $displayItemType;
	private $defaultDisplayed;
	private $label;
	private $helpText;
	
	/**
	 * @param Lstr $labelLstr
	 * @param string $displayItemType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(string $displayItemType, bool $defaultDisplayed, string $label, string $helpText = null) {
		ArgUtils::valEnum($displayItemType, SiStructureType::all());
		
		$this->displayItemType = $displayItemType;
		$this->defaultDisplayed = $defaultDisplayed;
		$this->label = $label;
		$this->helpText = $helpText;
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
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getHelpText() {
		return $this->helpText;
	}
}

