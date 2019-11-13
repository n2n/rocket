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
namespace rocket\ei\mask\model;

use rocket\ei\manage\gui\field\GuiPropPath;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\GuiException;
use rocket\si\meta\SiStructureType;

class DisplayStructure {
	private $displayItems = array();
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @param string $type
	 * @param string $label
	 * @param string $moduleNamespace
	 */
	public function addGuiPropPath(GuiPropPath $guiPropPath, string $type = null) {
		$this->displayItems[] = DisplayItem::create($guiPropPath, $type);
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @param string $type
	 * @param string $label
	 * @param string $moduleNamespace
	 */
	public function addDisplayStructure(DisplayStructure $displayStructure, string $type, string $label = null, 
			string $moduleNamespace = null) {
		$this->displayItems[] = DisplayItem::createFromDisplayStructure($displayStructure, $type, $label, $moduleNamespace);
	}
	
	/**
	 * @param DisplayItem $displayItem
	 */
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
	 * @return int
	 */
	public function size() {
		return count($this->displayItems);
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
	
	public function getAllGuiPropPaths() {
		$guiPropPaths = array();
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$guiPropPaths = array_merge($guiPropPaths, $displayItem->getDisplayStructure()->getAllGuiPropPaths());
			} else{
				$guiPropPaths[] = $displayItem->getGuiPropPath();
			}
		}
		return $guiPropPaths;
	}
		
	/**
	 * @return \rocket\ei\mask\model\DisplayStructure
	 */
	public function groupedItems() {
		$displayStructure = new DisplayStructure();
		
		$curDisplayStructure = null;
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->getSiStructureType() == SiStructureType::PANEL 
					&& $this->containsNonGrouped($displayItem)) {
				$displayStructure->addDisplayItem($displayItem->copy(SiStructureType::SIMPLE_GROUP));
				$curDisplayStructure = null;
				continue;
			}
			
			if ($displayItem->getSiStructureType() != SiStructureType::ITEM) {
				$displayStructure->addDisplayItem($displayItem);
				$curDisplayStructure = null;
				continue;
			}
			
			if ($curDisplayStructure === null) {
				$curDisplayStructure = new DisplayStructure();
				$displayStructure->addDisplayStructure($curDisplayStructure, SiStructureType::SIMPLE_GROUP);
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
			
			if ($displayItem->getSiStructureType() == SiStructureType::PANEL
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
			$groupType = $displayItem->getSiStructureType();
			
			if (!$displayItem->hasDisplayStructure()) {
				if ($groupType == SiStructureType::AUTONOMIC_GROUP) {
					$autonomicDs->addGuiPropPath($displayItem->getGuiPropPath(), SiStructureType::SIMPLE_GROUP, $displayItem->getLabel(), 
							$displayItem->getModuleNamespace());
				} else if ($displayItem->getSiStructureType() == $groupType) {
					$ds->displayItems[] = $displayItem;
				} else {
					$ds->addGuiPropPath($displayItem->getGuiPropPath(), $groupType, $displayItem->getLabel(), $displayItem->getModuleNamespace());	
				}
				continue;
			}
			
			$newDisplayStructure = new DisplayStructure();
			$this->roAutonomics($displayItem->getDisplayStructure()->getDisplayItems(), $newDisplayStructure, 
					($displayItem->getSiStructureType() == SiStructureType::MAIN_GROUP ? $newDisplayStructure : $autonomicDs));
			
			if ($displayItem->getSiStructureType() == SiStructureType::AUTONOMIC_GROUP) {
				$autonomicDs->addDisplayStructure($newDisplayStructure, SiStructureType::SIMPLE_GROUP, 
						$displayItem->getLabel(), $displayItem->getModuleNamespace());	
			} else {
				$ds->addDisplayStructure($newDisplayStructure, $displayItem->getSiStructureType(), $displayItem->getLabel(), 
						$displayItem->getModuleNamespace());
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
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return boolean
	 */
	public function containsGuiPropPathPrefix(GuiPropPath $guiPropPath) {
		return $this->containsLevelGuiPropPathPrefix($guiPropPath) 
				|| $this->containsSubGuiPropPathPrefix($guiPropPath);
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return boolean
	 */
	public function containsLevelGuiPropPathPrefix(GuiPropPath $guiPropPath) {
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->hasDisplayStructure()) continue;
			
			if ($displayItem->getGuiPropPath()->startsWith($guiPropPath, false)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return boolean
	 */
	public function containsSubGuiPropPathPrefix(GuiPropPath $guiPropPath) {
		foreach ($this->displayItems as $displayItem) {
			if (!$displayItem->hasDisplayStructure()) continue;
			
			if ($displayItem->getDisplayStructure()->containsGuiPropPathPrefix($guiPropPath)) {
				return true;
			}
		}
		
		return false;
	}
	
	public function purified(EiGui $eiGui) {
		return $this->rPurifyDisplayStructure($this, $eiGui);
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @param EiGui $eiGui
	 * @return \rocket\ei\mask\model\DisplayStructure
	 */
	private function rPurifyDisplayStructure($displayStructure, $eiGui) {
		$purifiedDisplayStructure = new DisplayStructure();
		
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$purifiedDisplayStructure->addDisplayStructure(
						$this->rPurifyDisplayStructure($displayItem->getDisplayStructure(), $eiGui),
						$displayItem->getSiStructureType(), $displayItem->getLabel(), $displayItem->getModuleNamespace());
				continue;
			}
			
			$guiPropAssembly = null;
			try {
				$guiPropAssembly = $eiGui->getGuiPropAssemblyByGuiPropPath($displayItem->getGuiPropPath());
			} catch (GuiException $e) {
				continue;
			}
			
			$purifiedDisplayStructure->addGuiPropPath($displayItem->getGuiPropPath(),
					$displayItem->getSiStructureType() ?? $guiPropAssembly->getDisplayDefinition()->getSiStructureType());
		}
		
		return $purifiedDisplayStructure;
	}
	
	/**
	 * @param EiGui $eiGui
	 * @return DisplayStructure
	 */
	public static function fromEiGui(EiGui $eiGui) {
		$displayStructure = new DisplayStructure();
		
		foreach ($eiGui->getGuiPropAssemblies() as $guiPropAssembly) {
			$displayStructure->addGuiPropPath($guiPropAssembly->getGuiPropPath(), 
					$guiPropAssembly->getDisplayDefinition()->getSiStructureType());
		}
		
		return $displayStructure;
	}
}