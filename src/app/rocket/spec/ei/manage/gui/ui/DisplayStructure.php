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
namespace rocket\spec\ei\manage\gui\ui;

use rocket\spec\ei\manage\gui\GuiIdPath;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\gui\EiEntryGui;

class DisplayStructure {
	private $displayItems = array();
	
	public function addGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null) {
		$this->displayItems[] = DisplayItem::createFromGuiIdPath($guiIdPath, $groupType);
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
			if ($displayItem->isGroup()) {
				$guiIdPaths = array_merge($guiIdPaths, $displayItem->getDisplayStructure()->getAllGuiIdPaths());
			} else{
				$guiIdPaths[] = $displayItem->getGuiIdPath();
			}
		}
		return $guiIdPaths;
	}
	
	const PURIFY_MODE_NO_GROUPS = 'noGroups';
	const PURIFY_MODE_GROUPS_IN_ROOT = 'groupsInRoot';
	
	public function purify(EiEntryGui $eiEntryGui) {
		$displayStructure = new DisplayStructure();
		
		foreach ($this->displayItems as $displayItem) {
			$eiEntryGui->getDisplayableByGuiIdPath($guiIdPath);
		}
	}

	public function withoutGroups() {
		$displayStructure = new DisplayStructure();
	
		$this->withoutGroups($displayStructure, $this->displayItems);
	
		return $displayStructure;
	}
	
	private function stripgGroups(DisplayStructure $displayStructure, array $orderItems) {
		foreach ($orderItems as $orderItem) {
			if (!$orderItem->isGroup()) {
				$displayStructure->displayItems[] = $orderItem;
				continue;
			}
				
			if ($orderItem->hasGuiSection()) {
				$this->stripgGroups($displayStructure, $orderItem->getGuiSection()->getDisplayItems());
				continue;
			}
			
			
			$orderItem = $orderItem->copy();
			$orderItem->setGroupType(null);
			$displayStructure->displayItems[] = $orderItem;
		}
	
	}
}