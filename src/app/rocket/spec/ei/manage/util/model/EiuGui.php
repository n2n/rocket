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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\DisplayDefinition;

class EiuGui {
	private $viewMode;
	private $eiuEntry;
// 	protected $eiMask;
	protected $eiSelectionGui;
	
	public function __construct($viewMode, $eiuEntry = null) {
		if ($eiuEntry !== null) {
			$this->eiuEntry = EiuFactory::buildEiuEntryFromEiArg($eiuEntry, null, 'eiuEntry', false);
		}
		
		if (is_numeric($viewMode)) {
			$this->viewMode = $viewMode;
			return;
		}
		
		if ($viewMode instanceof EntryGuiModel) {
			$this->viewMode = $viewMode->getEiSelectionGui()->getViewMode();
			if ($this->eiuEntry === null) {
				$this->eiuEntry = EiuFactory::buildEiuEntryFromEiArg($viewMode);
			}
		}
	}
	
	public function getEiuEntry(bool $required = true) {
		if (!$required || $this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		throw new EiuPerimeterException('No EiuGui provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
// 	public function getEiMask() {
// 		if ($this->eiMask !== null) {
// 			return $this->eiMask;
// 		}
		
// 		throw new IllegalStateException('No EiMask available.');
// 	}
	
	/**
	 * @param EntryGuiModel $entryGuiModel
	 * @param EiState $eiState
	 * @return EiuGui
	 */
	public static function from(EntryGuiModel $entryGuiModel, $eiState) {
		$entryGuiUtils = new EiuGui($entryGuiModel->getEiMapping(), 
				$entryGuiModel->getEiSelectionGui()->getViewMode(), $eiState);
		$entryGuiUtils->eiSelectionGui = $entryGuiModel->getEiSelectionGui();
		return $entryGuiUtils;
	}
	
	/**
	 * @return boolean
	 */
	public function isViewModeOverview() {
		return $this->viewMode == DisplayDefinition::VIEW_MODE_LIST_READ
				|| $this->viewMode == DisplayDefinition::VIEW_MODE_TREE_READ;
	}
}
