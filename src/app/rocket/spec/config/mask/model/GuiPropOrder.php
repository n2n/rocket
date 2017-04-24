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
namespace rocket\spec\config\mask\model;

use rocket\spec\ei\manage\gui\GuiIdPath;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\gui\EiEntryGui;

class DisplayStructure {
	private $orderItems = array();
	
	public function addGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null) {
		$this->orderItems[] = DisplayItem::createFromGuiIdPath($guiIdPath, $groupType);
	}
	
	public function addGuiGroup(GuiSection $guiSection, string $groupType = null) {
		$this->orderItems[] = DisplayItem::createFromGuiSection($guiSection);
	}
	
	public function addDisplayItem(DisplayItem $orderItem) {
		$this->orderItems[] = $orderItem;
	}
	
	/**
	 * @return DisplayItem []
	 */
	public function getDisplayItems() {
		return $this->orderItems;
	}
	
	public function setDisplayItems(array $orderItems) {
		ArgUtils::valArray($orderItems, DisplayItem::class);
		$this->orderItems = $orderItems;
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
		foreach ($this->orderItems as $orderItem) {
			if ($orderItem->isGroup()) {
				$guiIdPaths = array_merge($guiIdPaths, $orderItem->getGuiSection()->getDisplayStructure()->getAllGuiIdPaths());
			} else{
				$guiIdPaths[] = $orderItem->getGuiIdPath();
			}
		}
		return $guiIdPaths;
	}
	
	const PURIFY_MODE_NO_GROUPS = 'noGroups';
	const PURIFY_MODE_GROUPS_IN_ROOT = 'groupsInRoot';
	
	public function purify(EiEntryGui $eiEntryGui) {
		$guiPropOrder = new DisplayStructure();
		
		foreach ($this->orderItems as $orderItem) {
			$eiEntryGui->getDisplayableByGuiIdPath($guiIdPath);
		}
	}

	public function withoutGroups() {
		$guiPropOrder = new DisplayStructure();
	
		$this->withoutGroups($guiPropOrder, $this->orderItems);
	
		return $guiPropOrder;
	}
	
	private function stripgGroups(DisplayStructure $guiPropOrder, array $orderItems) {
		foreach ($orderItems as $orderItem) {
			if (!$orderItem->isGroup()) {
				$guiPropOrder->orderItems[] = $orderItem;
				continue;
			}
				
			if ($orderItem->hasGuiSection()) {
				$this->stripgGroups($guiPropOrder, $orderItem->getGuiSection()->getDisplayItems());
				continue;
			}
			
			
			$orderItem = $orderItem->copy();
			$orderItem->setGroupType(null);
			$guiPropOrder->orderItems[] = $orderItem;
		}
	
	}
}

class DisplayItem {
	protected $label;
	protected $groupType;
	protected $guiIdPath;
	protected $guiDisplayStructure;

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
	 * @param GuiSection $guiSection
	 * @return \rocket\spec\config\mask\DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $guiPropOrder, string $groupType, string $label = null) {
		$orderItem = new DisplayItem();
		$orderItem->guiPropOrder = $guiPropOrder;
		$orderItem->groupType = $groupType;
		$orderItem->label = $label;
		return $orderItem;
	}
	
	public function getGroupType() {
		if ($this->groupType !== null || $this->guiDisplayStructure === null) {
			return $this->groupType;
		}
		
		return $this->groupType;
	}
	
	public function isGroup() {
		return $this->guiDisplayStructure !== null || $this->groupType !== null;
	}
	
	public function hasDisplayStructure() {
		return $this->guiDisplayStructure !== null;
	}
	
	/**
	 * @return GuiSection
	 * @throws IllegalStateException
	 */
	public function getDisplayStructure() {
		if ($this->guiDisplayStructure !== null) {
			return $this->guiDisplayStructure;
		}
		
		throw new IllegalStateException();
	}
	
	public function getGuiIdPath() {
		if ($this->guiIdPath !== null) {
			return $this->guiIdPath;
		}
		
		throw new IllegalStateException();
	}
}
