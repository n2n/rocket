<?php
namespace rocket\ei\manage\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\si\meta\SiStructureType;

class DisplayDefinition {
	private $siStructureType;
	private $defaultDisplayed;
	private $label;
	private $helpText;
	
	/**
	 * @param Lstr $labelLstr
	 * @param string $siStructureType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(string $siStructureType, bool $defaultDisplayed, string $label, string $helpText = null) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		
		$this->siStructureType = $siStructureType;
		$this->defaultDisplayed = $defaultDisplayed;
		$this->label = $label;
		$this->helpText = $helpText;
	}
	
	/**
	 * @return string
	 */
	public function getSiStructureType(): string {
		return $this->siStructureType;
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

