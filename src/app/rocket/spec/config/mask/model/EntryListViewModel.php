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

use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\util\model\EiuEntryGui;

class EntryListViewModel {
	private $eiuFrame;
	private $eiuEntryGuis;
	private $guiDefinition;
	private $guiPropOrder;
	
	public function __construct(EiuFrame $eiuFrame, array $eiuEntryGuis, GuiDefinition $guiDefinition, 
			DisplayStructure $guiPropOrder) {
		$this->eiuFrame = $eiuFrame;
		$this->eiuEntryGuis = $eiuEntryGuis;
		$this->guiDefinition = $guiDefinition;
		$this->guiPropOrder = $guiPropOrder;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function getEiuFrame() {
		return $this->eiuFrame;
	}

	/**
	 * @return EiuEntryGui[]
	 */
	public function getEiuEntryGuis() {
		return $this->eiuEntryGuis;
	}

	public function getGuiDefinition() {
		return $this->guiDefinition;
	}

	public function getDisplayStructure() {
		return $this->guiPropOrder;
	}
}
