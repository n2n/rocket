<?php
namespace rocket\ui\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiStructureType;
use rocket\ui\si\meta\SiProp;
use n2n\l10n\N2nLocale;

class GuiProp {

	private SiProp $siProp;

	public function __construct(string $label, ?string $helpText = null) {
		$this->siProp = new SiProp($label);
		$this->siProp->setHelpText($helpText);
	}

	function setHelperText(?string $helperText): void {
		$this->siProp->setHelpText($helperText);
	}

	function setDescendantGuiPropNames(array $guiPropNames): void {
		$this->siProp->setDescendantPropNames($guiPropNames);
	}
}
