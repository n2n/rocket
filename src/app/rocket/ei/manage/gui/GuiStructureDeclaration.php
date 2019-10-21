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
use rocket\ei\manage\gui\field\GuiFieldPath;

class GuiStructureDeclaration {
	private $label;
	private $helpText;
	private $siStructureType;
	private $guiPropPath;
	private $children;
	
	/**
	 * @return string
	 */
	function getDisplayItemType() {
		return $this->displayItemType;
	}
	
	/**
	 * @return string|null
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string|null
	 */
	function getHelpText() {
		return $this->helpText;
	}
	
	/**
	 * @return string
	 */
	function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @return GuiFieldPath
	 */
	function getGuiPropPath() {
		return $this->guiPropPath;
	}
	
	/**
	 * @return GuiStructureDeclaration[]
	 */
	function getChildren() {
		return $this->children;
	}
	
	/**
	 * @param string $siStructureType
	 * @param GuiFieldPath $guiFieldPath
	 * @param string|null $label
	 * @param string|null $helpText
	 * @return GuiStructureDeclaration
	 */
	static function createField(GuiFieldPath $guiFieldPath, ?string $siStructureType, ?string $label, 
			string $helpText = null) {
		$gsd = new GuiStructureDeclaration();
		$gsd->label = $label;
		$gsd->helpText = $helpText;
		$gsd->siStructureType = $siStructureType;
		$gsd->guiPropPath = $guiFieldPath;
		return $gsd;
	}
	
	/**
	 * @param string $siStructureType
	 * @param GuiStructureDeclaration[] $children
	 * @param string|null $label
	 * @param string|null $helpText
	 * @return GuiStructureDeclaration
	 */
	static function createGroup(array $children, string $siStructureType, ?string $label, string $helpText = null) {
		ArgUtils::valArray($children, GuiStructureDeclaration::class);
		$gsd = new GuiStructureDeclaration();
		$gsd->siStructureType = $siStructureType;
		$gsd->children = $children;
		$gsd->label = $label;
		$gsd->helpText = $helpText;
		return $gsd;
	}
}

