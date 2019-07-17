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
namespace rocket\ei\util\gui;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiEntryGuiMulti;
use rocket\si\input\SiEntryInput;

class EiuEntryGuiMulti {
	/**
	 * @var EiEntryGuiMulti
	 */
	private $eiEntryGuiMulti;
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	
	/**
	 * @param EiEntryGuiMulti $eiEntryGuiMulti
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiEntryGuiMulti $eiEntryGuiMulti, EiuAnalyst $eiuAnalyst) {
		$this->eiEntryGuiMulti = $eiEntryGuiMulti;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGuiMulti
	 */
	function getEiEntryGuiMulti() {
		return $this->eiEntryGuiMulti;
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @return EiuEntryGuiMulti
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$this->eiEntryGuiMulti->handleSiEntryInput($siEntryInput);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuEntryGui[]
	 */
	function entryGuis() {
		$eiuEntryGuis = [];
		foreach ($this->eiEntryGuiMulti->getEiEntryGuis() as $eiTypeId => $eiEntryGui) {
			$eiuEntryGuis[$eiTypeId] = new EiuEntryGui($eiEntryGui, null, $this->eiuAnalyst);
		}
		return $eiuEntryGuis;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	function selectedEntryGui() {
		return new EiuEntryGui($this->eiEntryGuiMulti->getSelectedEiEntryGui(), null, $this->eiuAnalyst);
	}
}
