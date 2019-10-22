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
use rocket\si\meta\SiStructureType;
use rocket\ei\util\Eiu;

class DisplayConfig {
	private $compatibleViewModes;
	private $defaultDisplayedViewModes;
	
	private $siStructureType = SiStructureType::ITEM;
	
	/**
	 * @param int $compatibleViewModes
	 */
	public function __construct(int $compatibleViewModes) {
		$this->compatibleViewModes = $compatibleViewModes;
		$this->defaultDisplayedViewModes = $this->compatibleViewModes;
	}
	
	/**
	 * @param int $viewMode
	 * @return boolean
	 */
	public function isViewModeCompatible(int $viewMode) {
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
	
	/**
	 * @param int $viewModes
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setDefaultDisplayedViewModes(int $viewModes) {
		if ($viewModes & ~$this->compatibleViewModes) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->defaultDisplayedViewModes = $viewModes;
		
		return $this;
	}
	
	/**
	 * @param int $viewModes
	 * @param bool $defaultDisplayed
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function changeDefaultDisplayedViewModes(int $viewModes, bool $defaultDisplayed) {
	    ArgUtils::assertTrue((boolean) ($viewModes & ViewMode::all()), 'viewMode');
		
		if ($defaultDisplayed && ($viewModes & ~$this->compatibleViewModes)) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->changeDefaultDisplayed($viewModes, $defaultDisplayed);
		
		return $this;
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
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setListReadModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::COMPACT_READ, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setBulkyModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_READ, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setEditModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_EDIT, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setAddModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_ADD, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param string $siStructureType
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setSiStructureType(string $siStructureType) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		$this->dispayItemType = $siStructureType;
		return $this;
	}
	
	public function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @param int $viewMode
	 * @return DisplayDefinition|null
	 */
	function toDisplayDefinition(int $viewMode, string $label, string $helpText = null) {
		if (!$this->isViewModeCompatible($viewMode)) return null;
		
		return new DisplayDefinition($this->siStructureType,
				$this->isViewModeDefaultDisplayed($viewMode), $label, $helpText);
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\DisplayDefinition|NULL
	 */
	function buildDisplayDefinitionFromEiu(Eiu $eiu) {
		return $this->toDisplayDefinition($eiu->gui()->getViewMode(), $eiu->prop()->getLabel(), 
				$eiu->prop()->getHelpText());
	}
}
