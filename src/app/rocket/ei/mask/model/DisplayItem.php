<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use n2n\l10n\N2nLocale;
use rocket\si\meta\SiStructureType;

class DisplayItem {
	
	protected $label;
	protected $moduleNamespace;
	protected $siStructureType;
	protected $guiPropPath;
	protected $attrs;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiPropPath $guiPropPath
	 * @return DisplayItem
	 */
	public static function create(GuiPropPath $guiPropPath, string $siStructureType = null) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($siStructureType, SiStructureType::all(), null, true);
		$orderItem->siStructureType = $siStructureType;
		$orderItem->guiPropPath = $guiPropPath;
		return $orderItem;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return DisplayItem
	 * @deprecated
	 */
	public static function createFromGuiPropPath(GuiPropPath $guiPropPath, string $siStructureType = null) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($siStructureType, SiStructureType::all(), null, true);
		$orderItem->siStructureType = $siStructureType;
		$orderItem->guiPropPath = $guiPropPath;
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
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
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
		$displayItem->guiPropPath = $this->guiPropPath;
		ArgUtils::valEnum($siStructureType, SiStructureType::all(), null, true);
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
			throw new IllegalStateException('No labels for GuiPropPath DisplayItem.');
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
		return in_array($this->siStructureType, SiStructureType::groups());
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
	 * @return GuiPropPath
	 */
	public function getGuiPropPath() {
		if ($this->guiPropPath !== null) {
			return $this->guiPropPath;
		}

		throw new IllegalStateException();
	}
}