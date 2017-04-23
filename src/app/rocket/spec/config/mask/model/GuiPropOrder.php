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

class GuiPropOrder {
	private $orderItems = array();
	
	public function addGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null) {
		$this->orderItems[] = OrderItem::createFromGuiIdPath($guiIdPath, $groupType);
	}
	
	public function addGuiGroup(GuiSection $guiSection, string $groupType = null) {
		$this->orderItems[] = OrderItem::createFromGuiSection($guiSection);
	}
	
	public function addOrderItem(OrderItem $orderItem) {
		$this->orderItems[] = $orderItem;
	}
	
	/**
	 * @return OrderItem []
	 */
	public function getOrderItems() {
		return $this->orderItems;
	}
	
	public function setOrderItems(array $orderItems) {
		ArgUtils::valArray($orderItems, OrderItem::class);
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
				$guiIdPaths = array_merge($guiIdPaths, $orderItem->getGuiSection()->getGuiPropOrder()->getAllGuiIdPaths());
			} else{
				$guiIdPaths[] = $orderItem->getGuiIdPath();
			}
		}
		return $guiIdPaths;
	}
	
	const PURIFY_MODE_NO_GROUPS = 'noGroups';
	const PURIFY_MODE_GROUPS_IN_ROOT = 'groupsInRoot';
	
	public function purify(EiEntryGui $eiEntryGui) {
		$guiPropOrder = new GuiPropOrder();
		
		foreach ($this->orderItems as $orderItem) {
			$eiEntryGui->getDisplayableByGuiIdPath($guiIdPath);
		}
	}

	public function withoutGroups() {
		$guiPropOrder = new GuiPropOrder();
	
		$this->withoutGroups($guiPropOrder, $this->orderItems);
	
		return $guiPropOrder;
	}
	
	private function stripgGroups(GuiPropOrder $guiPropOrder, array $orderItems) {
		foreach ($orderItems as $orderItem) {
			if (!$orderItem->isGroup()) {
				$guiPropOrder->orderItems[] = $orderItem;
				continue;
			}
				
			if ($orderItem->hasGuiSection()) {
				$this->stripgGroups($guiPropOrder, $orderItem->getGuiSection()->getOrderItems());
				continue;
			}
			
			
			$orderItem = $orderItem->copy();
			$orderItem->setGroupType(null);
			$guiPropOrder->orderItems[] = $orderItem;
		}
	
	}
}

class OrderItem {
	protected $label;
	protected $groupType;
	protected $guiIdPath;
	protected $guiSection;

	private function __construct() {
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \rocket\spec\config\mask\OrderItem
	 */
	public static function createFromGuiIdPath(GuiIdPath $guiIdPath, string $groupType = null, string $label = null) {
		$orderItem = new OrderItem();
		$orderItem->label = $label;
		$orderItem->groupType = $groupType;
		$orderItem->guiIdPath = $guiIdPath;
		return $orderItem;
	}
	
	/**
	 * @param GuiSection $guiSection
	 * @return \rocket\spec\config\mask\OrderItem
	 */
	public static function createFromGuiSection(GuiSection $guiSection) {
		$orderItem = new OrderItem();
		$orderItem->guiSection = $guiSection;
		return $orderItem;
	}
	
	public function getGroupType() {
		if ($this->groupType !== null || $this->guiSection === null) {
			return $this->groupType;
		}
		
		return $this->groupType;
	}
	
	public function isGroup() {
		return $this->guiSection !== null || $this->groupType !== null;
	}
	
	public function hasGuiSection() {
		return $this->guiSection !== null;
	}
	
	/**
	 * @return GuiSection
	 * @throws IllegalStateException
	 */
	public function getGuiSection() {
		if ($this->guiSection !== null) {
			return $this->guiSection;
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
