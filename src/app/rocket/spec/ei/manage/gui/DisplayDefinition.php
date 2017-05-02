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
namespace rocket\spec\ei\manage\gui;

use n2n\reflection\ArgUtils;

class DisplayDefinition {
	const VIEW_MODE_LIST_READ = 1;
	const VIEW_MODE_LIST_EDIT = 2;
	const VIEW_MODE_LIST_ADD = 4;
	const VIEW_MODE_BULKY_READ = 64;
	const VIEW_MODE_BULKY_EDIT = 128;
	const VIEW_MODE_BULKY_ADD = 256;
	
	const READ_VIEW_MODES = 73;
	const EDIT_VIEW_MODES = 146;
	const ADD_VIEW_MODES = 292;
	
	const LIST_VIEW_MODES = 7;
	const TREE_VIEW_MODES = 56;
	const BULKY_VIEW_MODES = 448;
	
	const ALL_VIEW_MODES = 511;
	const NO_VIEW_MODES = 0;
	
	private $compatibleViewModes;
	private $defaultDisplayedViewModes;
	
	private $helpText;
	
	/**
	 * @param int $compatibleViewMode
	 */
	public function __construct(int $compatibleViewMode = self::ALL_VIEW_MODES) {
		$this->compatibleViewModes = $compatibleViewMode;
		$this->defaultDisplayedViewModes = $this->compatibleViewModes;
	}
	
	public function isViewModeCompatible($viewMode) {
		return (boolean) ($viewMode & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isListReadViewCompatible(): bool {
		return (boolean) (self::VIEW_MODE_LIST_READ & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isTreeReadViewCompatible(): bool {
		return (boolean) (self::VIEW_MODE_TREE_READ & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isBulkyReadViewCompatible(): bool {
		return (boolean) (self::VIEW_MODE_BULKY_READ & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isEditViewCompatible(): bool {
		return (boolean) (self::EDIT_VIEW_MODES & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isAddViewCompatible(): bool {
		return (boolean) (self::ADD_VIEW_MODES & $this->compatibleViewModes);
	}
	
	public function setDefaultDisplayedViewModes($viewModes) {
		if ($viewModes & ~$this->compatibleViewModes) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->defaultDisplayedViewModes = $viewModes;
	}
	
	public function changeDefaultDisplayedViewModes($viewModes, $defaultDisplayed) {
	    ArgUtils::assertTrue((boolean) ($viewModes & self::ALL_VIEW_MODES), 'viewMode');
		
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
		$this->changeDefaultDisplayed(self::VIEW_MODE_LIST_READ, $defaultDisplayaed);
	}
	
	public function setTreeReadModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(self::VIEW_MODE_TREE_READ, $defaultDisplayaed);
	}
	
	public function setBulkyModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(self::VIEW_MODE_BULKY_READ, $defaultDisplayaed);
	}
	
	public function setEditModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(self::EDIT_VIEW_MODES, $defaultDisplayaed);
	}
	
	public function setAddModeDefaultDisplayed($defaultDisplayaed) {
		$this->changeDefaultDisplayed(self::ADD_VIEW_MODES, $defaultDisplayaed);
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
	
	public static function getViewModes(): array {
		return array(self::VIEW_MODE_LIST_READ, self::VIEW_MODE_TREE_READ, self::VIEW_MODE_BULKY_READ, 
				self::VIEW_MODE_BULKY_EDIT, self::VIEW_MODE_BULKY_ADD);
	}
}
