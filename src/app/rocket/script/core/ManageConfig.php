<?php
namespace rocket\script\core;

use rocket\script\core\MenuGroup;
use n2n\core\config\source\ConfigSource;

class ManageConfig {
	const RAW_DATA_MENU_GROUPS_KEY = 'menuGroups';
	const RAW_DATA_UNSEALED_SCRIPTS_KEY = 'unsealedScripts';
	const RAW_DATA_MENU_POINT_SCRIPT_ID_KEY = 'scriptId';
	const RAW_DATA_MENU_POINT_LABEL_KEY = 'label';
	const RAW_DATA_MENU_POINT_MASK_ID_KEY = 'maskId';
	
	private $source;
	private $rawData;
	
	public function __construct(ConfigSource $configSource) {
		$this->source = $configSource;
		$this->rawData = $configSource->readArray();
		
		if (!isset($this->rawData[self::RAW_DATA_MENU_GROUPS_KEY]) || !is_array($this->rawData[self::RAW_DATA_MENU_GROUPS_KEY])
				|| !isset($this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY]) || !is_array($this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY])) {
			$this->clear();
		}
	}
	
	public function flush() {
		$this->source->writeArray($this->rawData);
	}
	
	public function clear() {
		$this->rawData[self::RAW_DATA_MENU_GROUPS_KEY] = array();
		$this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY] = array();
	}
	
	public function isScriptOfIdSealed($scriptId) {
		return !in_array($scriptId, $this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY]);
	}
	
	public function registerAsUnsealed($scriptId) {
		if (!$this->isScriptOfIdSealed($scriptId)) return;
		$this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY][] = $scriptId;
	}
	
	public function registerAsSealed($scriptId) {
		foreach ($this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY] as $key => $uScriptId) {
			if ($scriptId != $uScriptId) continue;
			unset($this->rawData[self::RAW_DATA_UNSEALED_SCRIPTS_KEY][$key]);
		}
	}
	
	public function extractMenuGroups() {
		$menuGroups = array();
	
		foreach ($this->rawData[self::RAW_DATA_MENU_GROUPS_KEY] as $label => $menuPointRawDatas) {
			// @todo JsonExtraction utils required $menuPoints could be a string due to misconfiguration
			if (!is_array($menuPointRawDatas)) continue;
			$menuPoints = array();
			foreach ((array) $menuPointRawDatas as $key => $menuPointRawData) {
				$menuItem = new MenuItem($key);
				
				if (is_scalar($menuPointRawData)) {
					$menuItem->setLabel($menuPointRawData); 
				} else if (is_array($menuPointRawData) 
						&& isset($menuPointRawData[self::RAW_DATA_MENU_POINT_SCRIPT_ID_KEY])) {
					$menuItem->setScriptId($menuPointRawData[self::RAW_DATA_MENU_POINT_SCRIPT_ID_KEY]);
					
					if (isset($menuPointRawData[self::RAW_DATA_MENU_POINT_LABEL_KEY])) {
						$menuItem->setLabel($menuPointRawData[self::RAW_DATA_MENU_POINT_LABEL_KEY]);
					}
					
					if (isset($menuPointRawData[self::RAW_DATA_MENU_POINT_MASK_ID_KEY])) {
						$menuItem->setMaskId($menuPointRawData[self::RAW_DATA_MENU_POINT_MASK_ID_KEY]);
					}
				} else {
					$menuItem->setScriptId($key);	
				}
			
				$menuPoints[$key] = $menuItem;
			}
			
			$menuGroups[] = new MenuGroup($label, $menuPoints);
		}
	
		return $menuGroups;
	}
	
	public function setMenuGroups(array $menuGroups) {
		$this->rawData[self::RAW_DATA_MENU_GROUPS_KEY] = array();
		foreach ($menuGroups as $menuGroup) {
			$this->rawData[self::RAW_DATA_MENU_GROUPS_KEY][$menuGroup->getLabel()] = array();
			foreach ($menuGroup->getMenuItems() as $scriptId => $label) {
				$this->rawData[self::RAW_DATA_MENU_GROUPS_KEY][$menuGroup->getLabel()][$scriptId] = $label;
			}
		}
	}
	
}