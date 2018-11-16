<?php
namespace rocket\ei\manage\gui\ui;

use rocket\ei\manage\gui\GuiPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\ArgUtils;
use n2n\l10n\N2nLocale;
use rocket\core\model\Rocket;

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
	protected $eiPropPath;
	protected $helpText;
	protected $attrs;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiPropPath $eiPropPath
	 * @return DisplayItem
	 */
	public static function create(GuiPropPath $eiPropPath, string $type = null, string $label = null, 
			string $moduleNamespace = null) {
		$orderItem = new DisplayItem();
		$orderItem->label = $label;
		$orderItem->moduleNamespace = $moduleNamespace;
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$orderItem->type = $type;
		$orderItem->eiPropPath = $eiPropPath;
		return $orderItem;
	}
	
	/**
	 * @param GuiPropPath $eiPropPath
	 * @return DisplayItem
	 * @deprecated
	 */
	public static function createFromGuiPropPath(GuiPropPath $eiPropPath, string $type = null, string $label = null) {
		$orderItem = new DisplayItem();
		$orderItem->label = $label;
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$orderItem->type = $type;
		$orderItem->eiPropPath = $eiPropPath;
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
	 * @param string|null $label
	 * @return DisplayItem
	 */
	public function copy(string $type = null, array $attrs = null, string $label = null, string $helpText = null, string $moduleNamespace = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $this->displayStructure;
		$displayItem->eiPropPath = $this->eiPropPath;
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$displayItem->type = $type ?? $this->type;
		$displayItem->label = $label ?? $this->label;
		$displayItem->moduleNamespace = $moduleNamespace ?? $this->moduleNamespace;
		$displayItem->helpText = $helpText ?? $this->helpText;
		$displayItem->attrs = $attrs ?? $this->attrs;
		return $displayItem;
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
	public function getHelpText() {
		return $this->helpText;
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
		if ($this->label === null) return null;
		
		if ($this->moduleNamespace === null) {
			return $this->label;
		}
		
		return Rocket::createLstr($this->label, $this->moduleNamespace)->t($n2nLocale);
	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string|null
	 */
	public function translateHelpText(N2nLocale $n2nLocale) {
		if ($this->helpText === null) return null;
		
		if ($this->moduleNamespace === null) {
			return $this->helpText;
		}
		
		return Rocket::createLstr($this->helpText, $this->moduleNamespace)->t($n2nLocale);
	}

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

	public function getGuiPropPath() {
		if ($this->eiPropPath !== null) {
			return $this->eiPropPath;
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