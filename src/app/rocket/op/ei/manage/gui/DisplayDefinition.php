<?php
namespace rocket\op\ei\manage\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiStructureType;

class DisplayDefinition {

	/**
	 * @param string|null $siStructureType
	 * @param bool $defaultDisplayed
	 * @param string|null $label
	 * @param string|null $helpText
	 */
	public function __construct(private ?string $siStructureType, private bool $defaultDisplayed,
			private ?string $label = null, private ?string $helpText = null) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all(), nullAllowed: true);
	}

	/**
	 * @return string|null
	 */
	public function getSiStructureType(): ?string {
		return $this->siStructureType;
	}
	
	/**
	 * @return bool
	 */
	public function isDefaultDisplayed(): bool {
		return $this->defaultDisplayed;
	}
	
	public function getLabel(): ?string {
		return $this->label;
	}
	
	public function getHelpText(): ?string {
		return $this->helpText;
	}
}
