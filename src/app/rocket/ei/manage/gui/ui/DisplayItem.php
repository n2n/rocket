<?php
namespace rocket\ei\manage\gui\ui;

use rocket\ei\manage\gui\GuiIdPath;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\ArgUtils;

class DisplayItem {
	const TYPE_SIMPLE_GROUP = 'simple-group';
	const TYPE_MAIN_GROUP = 'main-group';
	const TYPE_AUTONOMIC_GROUP = 'autonomic-group';
	const TYPE_LIGHT_GROUP = 'light-group';
	const TYPE_PANEL = 'panel';
	const TYPE_ITEM = 'item';

	protected $label;
	protected $type;
	protected $guiIdPath;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiIdPath $guiIdPath
	 * @return DisplayItem
	 */
	public static function createFromGuiIdPath(GuiIdPath $guiIdPath, string $type = null, string $label = null) {
		$orderItem = new DisplayItem();
		$orderItem->label = $label;
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		$orderItem->type = $type;
		$orderItem->guiIdPath = $guiIdPath;
		return $orderItem;
	}

	/**
	 * @param DisplayStructure $displayStructure
	 * @return DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $displayStructure, string $type, 
			string $label = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $displayStructure;
		ArgUtils::valEnum($type, self::getTypes());
		$displayItem->type = $type;
		$displayItem->label = $label;
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
	public function getType() {
		return $this->type;
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

	public function getGuiIdPath() {
		if ($this->guiIdPath !== null) {
			return $this->guiIdPath;
		}

		throw new IllegalStateException();
	}
	
	public static function getGroupTypes() {
		return array(self::TYPE_SIMPLE_GROUP, self::TYPE_MAIN_GROUP, self::TYPE_AUTONOMIC_GROUP,
				self::TYPE_LIGHT_GROUP);
	}
	
	public static function getTypes() {
		return array(self::TYPE_ITEM, self::TYPE_SIMPLE_GROUP, self::TYPE_MAIN_GROUP, self::TYPE_AUTONOMIC_GROUP,
				self::TYPE_LIGHT_GROUP, self::TYPE_PANEL);
	}
}