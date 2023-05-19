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
namespace rocket\op\ei\util\gui;

use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\EiGuiValueBoundaryAssembler;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\manage\gui\field\GuiField;

class EiuGuiEntryAssembler {
	private $eiGuiValueBoundaryAssembler;
	private $eiuGuiEntry;
	private $eiuAnalyst;
	
	public function __construct(EiGuiValueBoundaryAssembler $eiGuiValueBoundaryAssembler, ?EiuGuiEntry $eiuGuiEntry,
			EiuAnalyst $eiuAnalyst) {
		$this->eiGuiValueBoundaryAssembler = $eiGuiValueBoundaryAssembler;
		$this->eiuGuiEntry = $eiuGuiEntry;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\op\ei\manage\gui\EiGuiValueBoundaryAssembler
	 */
	public function getEiGuiValueBoundaryAssembler() {
		return $this->eiGuiValueBoundaryAssembler;
	}
	
	/**
	 * @return EiuGuiEntry 
	 */
	public function getEiuGuiEntry() {
		if ($this->eiuGuiEntry === null) {
			$this->eiuGuiEntry = new EiuGuiEntry($this->eiGuiValueBoundaryAssembler->getEiGuiValueBoundary(), $this->eiuAnalyst);
		}
		
		return $this->eiuGuiEntry;
	}
	
	/**
	 * @param DefPropPath|string $defPropPath
	 * @return GuiField
	 */
	public function assembleGuiField($defPropPath) {
		return $this->eiGuiValueBoundaryAssembler->assembleGuiField(DefPropPath::create($defPropPath));
	}
	
	/**
	 * @see EiGuiValueBoundaryAssembler::finlize()
	 */
	public function finalize() {
		$this->eiGuiValueBoundaryAssembler->finalize();
	}
}
