<?php
namespace rocket\spec\ei\manage\gui\ui;

use rocket\spec\ei\manage\gui\GuiIdPath;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\ArgUtils;

class DisplayItem {
	const TYPE_SIMPLE = 'simple';
	const TYPE_MAIN = 'main';
	const TYPE_AUTONOMIC = 'autonomic';
	const TYPE_NONE = 'none';

	protected $label;
	protected $groupType;
	protected $guiIdPath;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiIdPath $guiIdPath
	 * @return DisplayItem
	 */
	public static function createFromGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null, string $label = null) {
		$orderItem = new DisplayItem();
		$orderItem->label = $label;
		ArgUtils::valEnum($groupType, self::getTypes(), null, true);
		$orderItem->groupType = $groupType;
		$orderItem->guiIdPath = $guiIdPath;
		return $orderItem;
	}

	/**
	 * @param DisplayStructure $displayStructure
	 * @return DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $displayStructure, string $groupType, 
			string $label = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $displayStructure;
		ArgUtils::valEnum($groupType, self::getGroupTypes());
		$displayItem->groupType = $groupType;
		$displayItem->label = $label;
		return $displayItem;
	}
	
	public function getLabel() {
		return $this->label;
	}

	public function getGroupType() {
		return $this->groupType;
	}

	public function isGroup() {
		return $this->displayStructure !== null || ($this->groupType !== null && $this->groupType !== self::TYPE_NONE);
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
		return array(self::TYPE_SIMPLE, self::TYPE_MAIN, self::TYPE_AUTONOMIC);
	}
	
	public static function getTypes() {
		return array(self::TYPE_NONE, self::TYPE_SIMPLE, self::TYPE_MAIN, self::TYPE_AUTONOMIC);
	}
}