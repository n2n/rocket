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
namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\EiPropPath;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\manage\gui\ViewMode;
use rocket\si\content\impl\relation\SiEmbeddedEntry;

class EmbeddedGuiCollection {
	/**
	 * @var bool
	 */
	private $readOnly;
	/**
	 * @var bool
	 */
	private $summaryRequired;
	/** 
	 * @var EiuEntryGui[]
	 */
	private $eiuEntryGuis = [];

	function __construct(bool $readOnly, bool $summaryRequired) {
		$this->readOnly = $readOnly;
		$this->summaryRequired = $summaryRequired;
	}

	function clear() {
		$this->eiuEntryGuis = [];
	}

	/**
	 * @param EiuEntry $eiuEntry
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	function add(EiuEntry $eiuEntry) {
		return $this->eiuEntryGuis[] = $eiuEntry->newGui(true, $this->readOnly)->entryGui();
	}
	
	function addNews(EiuFrame $eiuFrame, int $num) {
		$eiuGuiModel = $eiuFrame->contextEngine()->newForgeMultiGuiModel(true, false);
		for ($i = 0; $i < $num; $i++) {
			$this->eiuEntryGuis[] = $eiuGuiModel->newEntryGui();
		}
	}
	
	function addNew(EiuFrame $eiuFrame) {
		return $this->eiuEntryGuis[] = $eiuFrame->newForgeMultiEntryGui(true, $this->readOnly);
	}
	
	function sort(EiPropPath $orderEiPropPath) {
		uasort($this->eiuEntryGuis, function($a, $b) use ($orderEiPropPath) {
			$aValue = $a->entry()->getScalarValue($orderEiPropPath);
			$bValue = $b->entry()->getScalarValue($orderEiPropPath);
			
			if ($aValue == $bValue) {
				return 0;
			}
			
			return ($aValue < $bValue) ? -1 : 1;
		});
	}
	
	/**
	 * @return int
	 */
	function count() {
		return count($this->eiuEntryGuis);
	}
	
	function createSiEmbeddedEntries() {
		return array_values(array_map(
				function ($eiuEntryGui) { return $this->createSiEmbeddeEntry($eiuEntryGui); },
				$this->eiuEntryGuis));
	}
	
	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntry($eiuEntryGui) {
		return new SiEmbeddedEntry(
				$eiuEntryGui->gui()->createBulkyEntrySiComp(false, false),
				($this->summaryRequired ?
						$eiuEntryGui->gui()->copy(false, true)->createCompactEntrySiComp(false, false):
						null));
	}
	
	/**
	 * @param string $id
	 * @return \rocket\ei\util\gui\EiuEntryGui|NULL
	 */
	function find(string $id) {
		foreach ($this->eiuEntryGuis as $eiuEntryGui) {
			if ($eiuEntryGui->entry()->hasId() && $id == $eiuEntryGui->entry()->getPid()) {
				return $eiuEntryGui;
			}
		}
		
		return null;
	}
}