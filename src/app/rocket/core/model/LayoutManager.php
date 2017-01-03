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
namespace rocket\core\model;

use rocket\core\model\UnknownMenuItemException;
use rocket\spec\config\SpecManager;
use rocket\core\model\MenuGroup;
use rocket\core\model\MenuItem;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\config\InvalidMenuConfigurationException;

class LayoutManager {
	private $scsd;
	private $specManager;
	
	private $startMenuItemLoaded = false;
	private $startMenuItem;
	private $menuGroups;
	
	public function __construct(LayoutConfigSourceDecorator $scsd, SpecManager $specManager) {
		$this->scsd = $scsd;
		$this->specManager = $specManager;
	}
	
	public function reset() {
		$this->startMenuItem = null;
		$this->menuGroups = null;
	}
	
	public function getStartMenuItem() {
		if ($this->startMenuItemLoaded) {
			return $this->startMenuItem;
		}
		
		if (null !== ($startMenuItemId = $this->scsd->extractStartMenuItemId())) {
			try {
				$this->startMenuItem = $this->specManager->getMenuItemById($startMenuItemId);
			} catch (UnknownMenuItemException $e) {
				throw new InvalidMenuConfigurationException('Failed to initialize start MenuItem.', 0, $e);
			}
		}
		
		$this->startMenuItemLoaded = true;
		return $this->startMenuItem;
	}
	
	public function setStartMenuItem(MenuItem $startMenuItem = null) {
		$this->startMenuItem = $startMenuItem;
	}
	
	public function getMenuGroups() {
		if ($this->menuGroups !== null) {
			return $this->menuGroups;
		}
		
		$this->menuGroups = array();
		foreach ($this->scsd->extractMenuGroups() as $menuGroupExtraction) {
			$menuGroup = new MenuGroup($menuGroupExtraction->getLabel());
				
			try {
				foreach ($menuGroupExtraction->getMenuItemIds() as $menuItemId => $label) {
					$menuGroup->addMenuItem($this->specManager->getMenuItemById($menuItemId), $label);
				}
			} catch (UnknownMenuItemException $e) {
				throw new InvalidMenuConfigurationException('Failed to initialize MenuGroup: '
						. $menuGroupExtraction->getLabel(), 0, $e);
			}
				
			$this->menuGroups[] = $menuGroup;
		}
		return $this->menuGroups;
	}
	
	public function setMenuGroups(array $menuGroups) {
		$this->menuGroups = $menuGroups;
	}
	
	public function flush() {
		throw new NotYetImplementedException();
		
// 		if ($this->startMenuItemLoaded) {
// 			$this->scsd->rawStartMenuItemId($bla);
// 		}
	}
}
