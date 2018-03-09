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
namespace rocket\ei\manage\util\model;

use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\EiEntryGuiAssembler;

class EiuEntryGuiAssembler {
	private $eiEntryGuiAssembler;
	private $eiuEntryGui;
	private $eiuFactory;
	
	public function __construct(EiEntryGuiAssembler $eiEntryGuiAssembler, EiuEntryGui $eiuEntryGui = null,
			EiuFactory $eiuFactory = null) {
		$this->eiEntryGuiAssembler = $eiEntryGuiAssembler;
		$this->eiuEntryGui = $eiuEntryGui;
		$this->eiuFactory = $eiuFactory;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGuiAssembler
	 */
	public function getEiEntryGuiAssembler() {
		return $this->eiEntryGuiAssembler;
	}
	
	/**
	 * @return EiuEntryGui 
	 */
	public function getEiuEntryGui() {
		if ($this->eiuEntryGui === null) {
			$this->eiuEntryGui = new EiuEntryGui($this->eiEntryGuiAssembler->getEiEntryGui(), null, $this->eiuFactory);
		}
		
		return $this->eiuEntryGui;
	}
	
	/**
	 * @param GuiIdPath|string $guiIdPath
	 * @return \rocket\ei\manage\gui\GuiFieldAssembly
	 */
	public function assembleGuiField($guiIdPath) {
		return $this->eiEntryGuiAssembler->assembleGuiField(GuiIdPath::create($guiIdPath));
	}
	
	/**
	 * @see EiEntryGuiAssembler::finlize()
	 */
	public function finalize() {
		$this->eiEntryGuiAssembler->finalize();
	}
}
