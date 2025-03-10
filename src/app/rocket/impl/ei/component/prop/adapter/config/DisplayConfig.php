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
use rocket\ui\gui\ViewMode;
use rocket\ui\si\meta\SiStructureType;
use rocket\op\ei\util\Eiu;

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
	
//	/**
//	 * @param int $compatibleViewModes
//	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
//	 */
//	function setCompatibleViewModes(int $compatibleViewModes) {
//		$this->compatibleViewModes = $compatibleViewModes;
//		$this->defaultDisplayedViewModes &= $compatibleViewModes;
//		return $this;
//	}
	
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
		return (boolean) (ViewMode::bulky() & $this->compatibleViewModes);
	}

	function areViewModesCompatible(int $viewModes): bool {
		return 0 === ($viewModes & ~$this->compatibleViewModes);
	}

	/**
	 * @param int $viewModes
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setDefaultDisplayedViewModes(int $viewModes): static {
		if (!$this->areViewModesCompatible($viewModes)) {
			throw new \InvalidArgumentException('View mode not allowed. Given: '
					. ViewMode::stringify($viewModes) . '; Allowed: '
					. ViewMode::stringify($this->compatibleViewModes));
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
		$this->siStructureType = $siStructureType;
		return $this;
	}
	
	public function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @param Eiu $eiu
	 * @return EiGuiPropSetup|null
	 */
	function buildGuiProp(Eiu $eiu, EiGuiField $eiGuiField) {
		$viewMode = $eiu->guiDefinition()->getViewMode();
		
		if (!$this->isViewModeCompatible($viewMode)) {
			return null;
		}
		
		return $eiu->factory()->newGuiProp($eiGuiField)
				->setDefaultDisplayed($this->isViewModeDefaultDisplayed($viewMode))
				->setSiStructureType($this->getSiStructureType())
				->toGuiProp();
	}
}
