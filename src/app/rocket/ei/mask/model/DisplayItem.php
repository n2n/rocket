<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\field\GuiFieldPath;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use n2n\l10n\N2nLocale;
use rocket\si\structure\SiStructureTypes;

class DisplayItem {
	
	protected $label;
	protected $moduleNamespace;
	protected $siStructureType;
	protected $guiFieldPath;
	protected $attrs;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return DisplayItem
	 */
	public static function create(GuiFieldPath $guiFieldPath, string $siStructureType = null) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($siStructureType, SiStructureTypes::all(), null, true);
		$orderItem->siStructureType = $siStructureType;
		$orderItem->guiFieldPath = $guiFieldPath;
		return $orderItem;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return DisplayItem
	 * @deprecated
	 */
	public static function createFromGuiFieldPath(GuiFieldPath $guiFieldPath, string $siStructureType = null) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($siStructureType, SiStructureTypes::all(), null, true);
		$orderItem->siStructureType = $siStructureType;
		$orderItem->guiFieldPath = $guiFieldPath;
		return $orderItem;
	}

	/**
	 * @param DisplayStructure $displayStructure
	 * @return DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $displayStructure, string $siStructureType, 
			string $label = null, string $moduleNamespace = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $displayStructure;
		ArgUtils::valEnum($siStructureType, SiStructureTypes::all());
		$displayItem->siStructureType = $siStructureType;
		$displayItem->label = $label;
		$displayItem->moduleNamespace = $moduleNamespace;
		return $displayItem;
	}
	
	/**
	 * @param string|null $type
	 * @param string|null $labelLstr
	 * @return DisplayItem
	 */
	public function copy(string $siStructureType = null, array $attrs = null/*, Lstr $labelLstr = null*/) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $this->displayStructure;
		$displayItem->guiFieldPath = $this->guiFieldPath;
		ArgUtils::valEnum($siStructureType, SiStructureTypes::all(), null, true);
		$displayItem->siStructureType = $siStructureType ?? $this->siStructureType;
// 		$displayItem->labelLstr = $labelLstr ?? $this->labelLstr;
		$displayItem->attrs = $attrs ?? $this->attrs;
		return $displayItem;
	}
	
	/**
	 * @return Lstr|null
	 */
	public function getLabelLstr() {
		if (!$this->hasDisplayStructure()) {
			throw new IllegalStateException('No labels for GuiFieldPath DisplayItem.');
		}
		
		if ($this->label === null) return null;
		
		if ($this->moduleNamespace === null) {
			return Lstr::create($this->label);
		}
				
		return Rocket::createLstr($this->label, $this->moduleNamespace);
	}
	
	/**
	 * @return string|null
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string|null
	 */
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string|null
	 */
	public function translateLabel(N2nLocale $n2nLocale) {
		$lstr = $this->getLabelLstr();
		if ($lstr !== null) {
			return $lstr->t($n2nLocale);
		}
		return null;
	}
	
// 	/**
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string|null
// 	 */
// 	public function translateHelpText(N2nLocale $n2nLocale) {
// 		if ($this->helpText === null) return null;
		
// 		if ($this->moduleNamespace === null) {
// 			return $this->helpText;
// 		}
		
// 		return Rocket::createLstr($this->helpText, $this->moduleNamespace)->t($n2nLocale);
// 	}

	/**
	 * @return string|null
	 */
	public function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @return array|null
	 */
	public function getAttrs() {
		return $this->attrs;
	}

	/**
	 * @return boolean
	 */
	public function isGroup() {
		return in_array($this->siStructureType, SiStructureTypes::groups());
	}

	public function hasDisplayStructure() {
		return $this->displayStructure !== null;
	}

	/**
	 * @return DisplayStructure
	 * @throws IllegalStateException
	 */
	public function getDisplayStructure() {
		if ($this->displayStructure !== null) {
			return $this->displayStructure;
		}

		throw new IllegalStateException();
	}

	/**
	 * @throws IllegalStateException
	 * @return GuiFieldPath
	 */
	public function getGuiFieldPath() {
		if ($this->guiFieldPath !== null) {
			return $this->guiFieldPath;
		}

		throw new IllegalStateException();
	}
}