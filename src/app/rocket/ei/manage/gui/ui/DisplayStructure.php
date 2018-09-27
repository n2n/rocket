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
	
	public function addGuiIdPath(GuiIdPath $guiIdPath, string $type = null, string $label = null) {
		$this->displayItems[] = DisplayItem::create($guiIdPath, $type, $label);
	}
	
	public function addDisplayStructure(DisplayStructure $displayStructure, string $type, string $label = null) {
		$this->displayItems[] = DisplayItem::createFromDisplayStructure($displayStructure, $type, $label);
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
		
	/**
	 * @return \rocket\ei\manage\gui\ui\DisplayStructure
	 */
	public function groupedItems() {
		$displayStructure = new DisplayStructure();
		
		$curDisplayStructure = null;
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->getType() == DisplayItem::TYPE_PANEL 
					&& $this->containsNonGrouped($displayItem)) {
				$displayStructure->addDisplayItem($displayItem->copy(DisplayItem::TYPE_SIMPLE_GROUP));
				$curDisplayStructure = null;
				continue;
			}
			
			if ($displayItem->getType() != DisplayItem::TYPE_ITEM) {
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
	
	/**
	 * @param DisplayItem $displayItem
	 * @return boolean
	 */
	private function containsNonGrouped(DisplayItem $displayItem) {
		if (!$displayItem->hasDisplayStructure()) return false;
		
		foreach ($displayItem->getDisplayStructure()->getDisplayItems() as $displayItem) {
			if ($displayItem->isGroup()) continue;
			
			if ($displayItem->getType() == DisplayItem::TYPE_PANEL
					&& !$this->containsNonGrouped($displayItem)) {
				continue;
			}
			
			return true;
		}
		
		return false;
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
	
	public function withContainer(string $type, string $label, array $attrs = null) {
		if (count($this->displayItems) != 1 
				|| $this->displayItems[0]->getType() != $type) {
			$ds = new DisplayStructure();
			$ds->addDisplayStructure($this, $type, $label, $attrs);
			return $ds;
		}
		
		if ($this->displayItems[0]->getLabel() == $label 
				&& $this->displayItems[0]->getAttrs() === $attrs) {
			return $this;
		}
		
		$ds = new DisplayStructure();
		$ds->addDisplayItem($this->displayItems[0]->copy($type, $label, $attrs));
		return $ds;
	}

	public function withoutSubStructures() {
		$displayStructure = new DisplayStructure();
	
		$this->stripSubStructures($displayStructure, $this->displayItems);
	
		return $displayStructure;
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @param DisplayItem[] $displayItems
	 */
	private function stripSubStructures(DisplayStructure $displayStructure, array $displayItems) {
		foreach ($displayItems as $displayItem) {
			if (!$displayItem->hasDisplayStructure()) {
				$displayStructure->displayItems[] = $displayItem;
				continue;
			}
				
			$this->stripSubStructures($displayStructure, $displayItem->getDisplayStructure()->getDisplayItems());			
		}
	}
}