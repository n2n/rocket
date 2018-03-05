<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\gui\ui;

use rocket\ei\manage\gui\GuiIdPath;
use n2n\reflection\ArgUtils;

class DisplayStructure {
	private $displayItems = array();
	
	public function addGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null, string $label = null) {
		$this->displayItems[] = DisplayItem::createFromGuiIdPath($guiIdPath, $groupType, $label);
	}
	
	public function addDisplayStructure(DisplayStructure $displayStructure, string $groupType, string $label = null) {
		$this->displayItems[] = DisplayItem::createFromDisplayStructure($displayStructure, $groupType, $label);
	}
	
	public function addDisplayItem(DisplayItem $displayItem) {
		$this->displayItems[] = $displayItem;
	}
	
	/**
	 * @return DisplayItem[]
	 */
	public function getDisplayItems() {
		return $this->displayItems;
	}
	
	/**
	 * @param DisplayItem[] $displayItems
	 */
	public function setDisplayItems(array $displayItems) {
		ArgUtils::valArray($displayItems, DisplayItem::class);
		$this->displayItems = $displayItems;
	}
	
// 	public function containsAsideGroup() {
// 		foreach ($this->orderItems as $orderItem) {
// 			if ($orderItem->isSection() && $orderItem->getGuiSection()->getType() == GuiSection::ASIDE) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
	
	public function getAllGuiIdPaths() {
		$guiIdPaths = array();
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$guiIdPaths = array_merge($guiIdPaths, $displayItem->getDisplayStructure()->getAllGuiIdPaths());
			} else{
				$guiIdPaths[] = $displayItem->getGuiIdPath();
			}
		}
		return $guiIdPaths;
	}
		
	public function grouped() {
		$displayStructure = new DisplayStructure();
		
		$curDisplayStructure = null;
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->isGroup()) {
				$displayStructure->addDisplayItem($displayItem);
				$curDisplayStructure = null;
				continue;
			}
			
			if ($curDisplayStructure === null) {
				$curDisplayStructure = new DisplayStructure();
				$displayStructure->addDisplayStructure($curDisplayStructure, DisplayItem::TYPE_SIMPLE_GROUP);
			}
			
			$curDisplayStructure->addDisplayItem($displayItem);
		}
			
		return $displayStructure;
	}
	
	public function whitoutAutonomics() {
		$displayStructure = new DisplayStructure();
		
		$this->roAutonomics($this->displayItems, $displayStructure, $displayStructure);
				
		return $displayStructure;
	}
	
	private function roAutonomics(array $displayItems, DisplayStructure $ds, DisplayStructure $autonomicDs) {
		foreach ($displayItems as $displayItem) {
			$groupType = $displayItem->getType();
			
			if (!$displayItem->hasDisplayStructure()) {
				if ($groupType == DisplayItem::TYPE_AUTONOMIC_GROUP) {
					$autonomicDs->addGuiIdPath($displayItem->getGuiIdPath(), DisplayItem::TYPE_SIMPLE_GROUP, $displayItem->getLabel());
				} else if ($displayItem->getType() == $groupType) {
					$ds->displayItems[] = $displayItem;
				} else {
					$ds->addGuiIdPath($displayItem->getGuiIdPath(), $groupType, $displayItem->getLabel());	
				}
				continue;
			}
			
			$newDisplayStructure = new DisplayStructure();
			$this->roAutonomics($displayItem->getDisplayStructure()->getDisplayItems(), $newDisplayStructure, 
					($displayItem->getType() == DisplayItem::TYPE_MAIN_GROUP ? $newDisplayStructure : $autonomicDs));
			
			if ($displayItem->getType() == DisplayItem::TYPE_AUTONOMIC_GROUP) {
				$autonomicDs->addDisplayStructure($newDisplayStructure, DisplayItem::TYPE_SIMPLE_GROUP, $displayItem->getLabel());	
			} else {
				$ds->addDisplayStructure($newDisplayStructure, $displayItem->getType(), $displayItem->getLabel());
			}
		}
	}

	public function withoutGroups() {
		$displayStructure = new DisplayStructure();
	
		$this->stripGroups($displayStructure, $this->displayItems);
	
		return $displayStructure;
	}
	
	private function stripGroups(DisplayStructure $displayStructure, array $displayItems) {
		foreach ($displayItems as $displayItem) {
			if (!$displayItem->isGroup()) {
				$displayStructure->displayItems[] = $displayItem;
				continue;
			}
				
			if ($displayItem->hasDisplayStructure()) {
				$this->stripGroups($displayStructure, $displayItem->getDisplayStructure()->getDisplayItems());
				continue;
			}
			
			$displayStructure->addGuiIdPath($displayItem->getGuiIdPath(), DisplayItem::TYPE_ITEM, 
					$displayItem->getLabel());
		}
	}
}