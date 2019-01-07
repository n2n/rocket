<?php
namespace rocket\ei\manage\gui\ui;

use rocket\ei\manage\gui\GuiFieldPath;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use n2n\l10n\N2nLocale;

class DisplayItem {
	const TYPE_SIMPLE_GROUP = 'simple-group';
	const TYPE_MAIN_GROUP = 'main-group';
	const TYPE_AUTONOMIC_GROUP = 'autonomic-group';
	const TYPE_LIGHT_GROUP = 'light-group';
	const TYPE_PANEL = 'panel';
	const TYPE_ITEM = 'item';

	protected $label;
	protected $moduleNamespace;
	protected $type;
	protected $guiFieldPath;
	protected $attrs;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return DisplayItem
	 */
	public static function create(GuiFieldPath $guiFieldPath, string $type = null) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$orderItem->type = $type;
		$orderItem->guiFieldPath = $guiFieldPath;
		return $orderItem;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return DisplayItem
	 * @deprecated
	 */
	public static function createFromGuiFieldPath(GuiFieldPath $guiFieldPath, string $type = null) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$orderItem->type = $type;
		$orderItem->guiFieldPath = $guiFieldPath;
		return $orderItem;
	}

	/**
	 * @param DisplayStructure $displayStructure
	 * @return DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $displayStructure, string $type, 
			string $label = null, string $moduleNamespace = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $displayStructure;
		ArgUtils::valEnum($type, self::getTypes());
		$displayItem->type = $type;
		$displayItem->label = $label;
		$displayItem->moduleNamespace = $moduleNamespace;
		return $displayItem;
	}
	
	/**
	 * @param string|null $type
	 * @param string|null $labelLstr
	 * @return DisplayItem
	 */
	public function copy(string $type = null, array $attrs = null/*, Lstr $labelLstr = null*/) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $this->displayStructure;
		$displayItem->guiFieldPath = $this->guiFieldPath;
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$displayItem->type = $type ?? $this->type;
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
	public function getType() {
		return $this->type;
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
		return in_array($this->type, DisplayItem::getGroupTypes());
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
	
	/**
	 * @return string[]
	 */
	public static function getGroupTypes() {
		return array(self::TYPE_SIMPLE_GROUP, self::TYPE_MAIN_GROUP, self::TYPE_AUTONOMIC_GROUP,
				self::TYPE_LIGHT_GROUP);
	}
	
	/**
	 * @return string[]
	 */
	public static function getTypes() {
		return array(self::TYPE_ITEM, self::TYPE_SIMPLE_GROUP, self::TYPE_MAIN_GROUP, self::TYPE_AUTONOMIC_GROUP,
				self::TYPE_LIGHT_GROUP, self::TYPE_PANEL);
	}
}