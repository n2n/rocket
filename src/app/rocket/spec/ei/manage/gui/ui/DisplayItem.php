<?php
namespace rocket\spec\ei\manage\gui\ui;

use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use n2n\util\ex\IllegalStateException;

class DisplayItem {
	const TYPE_SIMPLE = 'simple';
	const TYPE_MAIN = 'main';
	const TYPE_AUTONOMIC = 'autonomic';

	protected $label;
	protected $groupType;
	protected $guiIdPath;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \rocket\spec\config\mask\DisplayItem
	 */
	public static function createFromGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null, string $label = null) {
		$orderItem = new DisplayItem();
		$orderItem->label = $label;
		$orderItem->groupType = $groupType;
		$orderItem->guiIdPath = $guiIdPath;
		return $orderItem;
	}

	/**
	 * @param DisplayStructure $displayStructure
	 * @return \rocket\spec\config\mask\DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $displayStructure, string $groupType, 
			string $label = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $displayStructure;
		$displayItem->groupType = $groupType;
		$displayItem->label = $label;
		return $displayItem;
	}
	
	public function getLabel() {
		return $this->label;
	}

	public function getGroupType() {
		if ($this->groupType !== null || $this->displayStructure === null) {
			return $this->groupType;
		}

		return self::TYPE_SIMPLE;
	}

	public function isGroup() {
		return $this->displayStructure !== null || $this->groupType !== null;
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
}