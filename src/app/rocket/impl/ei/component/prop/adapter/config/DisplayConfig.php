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
namespace rocket\impl\ei\component\prop\adapter\config;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\component\prop\EiProp;
use rocket\ei\manage\gui\ui\DisplayItem;

class DisplayConfig {
	private $compatibleViewModes;
	private $defaultDisplayedViewModes;
	
	private $displayItemType = DisplayItem::TYPE_ITEM;
	
	private $helpText;
	
	/**
	 * @param int $compatibleViewModes
	 */
	public function __construct(int $compatibleViewModes) {
		$this->compatibleViewModes = $compatibleViewModes;
		$this->defaultDisplayedViewModes = $this->compatibleViewModes;
	}
	
	public function isViewModeCompatible($viewMode) {
		return (boolean) ($viewMode & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isCompactViewCompatible(): bool {
		return (boolean) (ViewMode::compact() & $this->compatibleViewModes);
	}
		
	/**
	 * @return boolean
	 */
	public function isBulkyViewCompatible(): bool {
		return (boolean) (self::BULKY_VIEW_MODES & $this->compatibleViewModes);
	}
	
	public function setDefaultDisplayedViewModes($viewModes) {
		if ($viewModes & ~$this->compatibleViewModes) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->defaultDisplayedViewModes = $viewModes;
	}
	
	public function changeDefaultDisplayedViewModes($viewModes, $defaultDisplayed) {
	    ArgUtils::assertTrue((boolean) ($viewModes & ViewMode::all()), 'viewMode');
		
		if ($defaultDisplayed && ($viewModes & ~$this->compatibleViewModes)) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->changeDefaultDisplayed($viewModes, $defaultDisplayed);
	}
	
	private function changeDefaultDisplayed($viewModes, $defaultDisplayed) {
		if ($defaultDisplayed) {
			$this->defaultDisplayedViewModes |= $viewModes;
		} else {
			$this->defaultDisplayedViewModes &= ~$viewModes;
		}
	}
	
	public function isViewModeDefaultDisplayed($viewMode) {
		return (boolean) ($viewMode & $this->defaultDisplayedViewModes);
	}
	
	public function setListReadModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::COMPACT_READ, $defaultDisplayaed);
	}
	
	public function setBulkyModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_READ, $defaultDisplayaed);
	}
	
	public function setEditModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_EDIT, $defaultDisplayaed);
	}
	
	public function setAddModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_ADD, $defaultDisplayaed);
	}
	
	public function setDisplayItemType(string $displayItemType) {
		ArgUtils::valEnum($displayItemType, DisplayItem::getTypes());
		$this->dispayItemType = $displayItemType;
	}
	
	public function getDisplayItemType() {
		return $this->displayItemType;
	}
	
	/**
	 * @return string
	 */
	public function getHelpText() {
		return $this->helpText;
	}

	/**
	 * @param string $helpText
	 */
	public function setHelpText($helpText) {
		$this->helpText = $helpText;
	}
	
	/**
	 * @param int $viewMode
	 * @return DisplayDefinition|null
	 */
	public function toDisplayDefinition(EiProp $eiProp, int $viewMode, string $groupType = DisplayItem::TYPE_ITEM) {
		if (!$this->isViewModeCompatible($viewMode)) return null;
		
		return new DisplayDefinition($groupType,
				$this->isViewModeDefaultDisplayed($viewMode), $this->helpText);
	}
}
