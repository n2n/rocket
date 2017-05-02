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

use rocket\spec\ei\EiDef;
use rocket\spec\ei\component\command\control\PartialControlComponent;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\command\control\OverallControlComponent;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\manage\gui\ui\DisplayStructure;

class DisplayScheme {
	private $overviewDisplayStructure;
	private $bulkyDisplayStructure;
	private $detailDisplayStructure;
	private $editDisplayStructure;
	private $addDisplayStructure;
	
	private $partialControlOrder;
	private $overallControlOrder;
	private $entryControlOrder;
	

	public function getOverviewDisplayStructure() {
		return $this->overviewDisplayStructure;
	}
	
	public function setOverviewDisplayStructure(DisplayStructure $overviewDisplayStructure = null) {
		$this->overviewDisplayStructure = $overviewDisplayStructure;
	}
	
	public function getBulkyDisplayStructure() {
		return $this->bulkyDisplayStructure;
	}
	
	public function setBulkyDisplayStructure(DisplayStructure $bulkyDisplayStructure = null) {
		$this->bulkyDisplayStructure = $bulkyDisplayStructure;
	}
	
	public function getDetailDisplayStructure() {
		return $this->detailDisplayStructure;
	}
	
	public function setDetailDisplayStructure(DisplayStructure $detailDisplayStructure = null) {
		$this->detailDisplayStructure = $detailDisplayStructure;
	}
	
	public function getEditDisplayStructure() {
		return $this->editDisplayStructure;
	}
	
	public function setEditDisplayStructure(DisplayStructure $editDisplayStructure = null) {
		$this->editDisplayStructure = $editDisplayStructure;
	}
	
	public function getAddDisplayStructure() {
		return $this->addDisplayStructure;
	}
	
	public function setAddDisplayStructure(DisplayStructure $addDisplayStructure = null) {
		$this->addDisplayStructure = $addDisplayStructure;
	}
	
// 	const BUTTON_ID_PARTIAL_SEPARATOR = '?PARTIAL?';
// 	const BUTTON_ID_OVERALL_SEPARATOR = '?OVERALL?';
// 	const BUTTON_ID_ENTRY_SEPARATOR = '?ENTRY?';
	
	/**
	 * @param array $controls
	 * @param array $order
	 * @return array
	 */
	
	
	public function getPartialControlOrder() {
		return $this->partialControlOrder;
	}
	
	public function setPartialControlOrder(ControlOrder $partialControlOrder = null) {
		$this->partialControlOrder = $partialControlOrder;
	}
	
	public function getOverallControlOrder() {
		return $this->overallControlOrder;
	}
	
	public function setOverallControlOrder(ControlOrder $overallControlOrder = null) {
		$this->overallControlOrder = $overallControlOrder;
	}
	
	public function getEntryControlOrder() {
		return $this->entryControlOrder;
	}
	
	public function setEntryControlOrder(ControlOrder $entryControlOrder = null) {
		$this->entryControlOrder = $entryControlOrder;
	}
	
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return array
	 */
	public static function buildPartialControlMap(EiDef $eiDef, N2nLocale $n2nLocale) {
		$labels = array();
	
		foreach ($eiDef->getEiCommandCollection() as $eiCommandId => $eiCommand) {
			if (!($eiCommand instanceof PartialControlComponent)) continue;
				
			foreach ($eiCommand->getPartialControlOptions($n2nLocale) as $controlId => $label) {
				$labels[ControlOrder::buildControlId($eiCommandId, $controlId)] = $label;
			}
		}
	
		if ($this->partialControlOrder === null) return $labels;
		
		return $this->partialControlOrder->sort($labels);
	}
	/**
	 * @param N2nLocale $n2nLocale
	 * @return array
	 */
	public static function buildOverallControlMap(EiDef $eiDef, N2nLocale $n2nLocale) {
		$labels = array();
	
		foreach ($this->eiType->getEiCommandCollection() as $eiCommandId => $eiCommand) {
			if (!($eiCommand instanceof OverallControlComponent)) continue;
				
			foreach ($eiCommand->getOverallControlOptions($n2nLocale) as $controlId => $label) {
				$labels[ControlOrder::buildControlId($eiCommandId, $controlId)] = $label;
			}
		}
	
		if ($this->overallControlOrder === null) return $labels;
		
		return $this->overallControlOrder->sort($labels);
	}
	/**
	 * @param N2nLocale $n2nLocale
	 * @return array
	 */
	public static function buildEntryControlMap(EiDef $eiDef, N2nLocale $n2nLocale) {
		$labels = array();
	
		foreach ($this->eiType->getEiCommandCollection() as $eiCommandId => $eiCommand) {
			if (!($eiCommand instanceof EntryControlComponent)) continue;
				
			foreach ($eiCommand->getEntryControlOptions($n2nLocale) as $controlId => $label) {
				$labels[ControlOrder::buildControlId($eiCommandId, $controlId)] = $label;
			}
		}
		
		if ($this->entryControlOrder === null) return $labels;
	
		return $this->entryControlOrder->sort($labels);
	}
}
