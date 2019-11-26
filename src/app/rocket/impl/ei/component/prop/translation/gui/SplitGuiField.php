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
namespace rocket\impl\ei\component\prop\translation\gui;


use rocket\ei\manage\gui\GuiFieldMap;
use rocket\ei\manage\gui\field\GuiField;
use rocket\si\content\SiField;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\si\content\impl\SiFields;

class SplitGuiField implements GuiField {
	private $lted;
	private $guiPropPath;
	
	function __construct(LazyTranslationEssentialsDeterminer $lted, GuiPropPath $guiPropPath) {
		$this->lted = $lted;
		$this->guiPropPath = $guiPropPath;
		$this->splitIn =  SiFields::splitIn();
	}
	
	function getSiField(): ?SiField {
		return splitIn;
	}
	
	function save() {
		$this->forkGuiFieldMap->save();
		
		$this->lted->save();
	}
	
	function getContextSiFields(): array {
		return [];
	}

	function setForkGuiFieldMap(?GuiFieldMap $forkGuiFieldMap) {
		$this->forkGuiFieldMap = $forkGuiFieldMap;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return $this->forkGuiFieldMap;
	}

}
