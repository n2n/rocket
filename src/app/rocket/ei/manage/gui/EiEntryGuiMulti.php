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
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\si\input\SiEntryInput;
use rocket\si\input\CorruptedSiInputDataException;
use n2n\util\ex\IllegalStateException;
use rocket\si\content\SiIdentifier;
use rocket\si\content\SiEntry;
use rocket\ei\EiType;

class EiEntryGuiMulti {
	/**
	 * @var EiType
	 */
	private $contextEiType;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @var string|null
	 */
	private $selectedEiTypeId = null;
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis;
	
	/**
	 * @param EiEntryGui[]
	 */
	function __construct(EiType $contextEiType, int $viewMode, array $eiEntryGuis) {
		$this->contextEiType = $contextEiType;
		$this->viewMode = $viewMode;
		$this->setEiEntryGuis($eiEntryGuis);
	}
	
	/**
	 * @param EiEntryGui[] $eiEntryGuis
	 */
	function setEiEntryGuis(array $eiEntryGuis) {
		foreach ($eiEntryGuis as $eiEntryGui) {
			ArgUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
			$this->putEiEntryGui($eiEntryGui);
		}
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	function putEiEntryGui(EiEntryGui $eiEntryGui) {
		$eiTypeId = $eiEntryGui->getEiEntry()->getEiType()->getId();
		
		$this->eiEntryGuis[$eiTypeId] = $eiEntryGui;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGui[]
	 */
	function getEiEntryGuis() {
		return $this->eiEntryGuis;
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$eiTypeId = $siEntryInput->getTypeId();
		
		if (!isset($this->eiEntryGuis[$eiTypeId])) {
			throw new CorruptedSiInputDataException('EiType not available: ' . $eiTypeId);
		}
		
		$this->selectedEiTypeId = $eiTypeId; 
		$this->eiEntryGuis[$eiTypeId]->handleSiEntryInput($siEntryInput);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function getSelectedEiEntryGui() {
		if (!isset($this->eiEntryGuis[$this->selectedEiTypeId])) {
			throw new IllegalStateException('No selection');
		}
		
		return $this->eiEntryGuis[$this->selectedEiTypeId];
	}
	
	function createSiEntry() {
		$siEntry = new SiEntry(new SiIdentifier($this->contextEiType->getSupremeEiType()->getId(), null),
		    ViewMode::isReadOnly($this->viewMode), ViewMode::isBulky($this->viewMode));
		
		foreach ($this->eiEntryGuis as $eiEntryGui) {
			$siEntry->putBuildup($eiEntryGui->getEiEntry()->getEiType()->getId(), 
					$eiEntryGui->createSiTypeBuildup());
		}
		
		if (count($this->eiEntryGuis) == 1) {
			$siEntry->setSelectedTypeId(current($this->eiEntryGuis)->getEiEntry()->getEiType()->getId());
		}
		
		return $siEntry;
	}
}