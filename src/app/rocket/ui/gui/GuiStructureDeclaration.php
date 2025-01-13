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
namespace rocket\ui\gui;

use n2n\util\type\ArgUtils;
use rocket\ui\gui\field\GuiPropPath ;
use n2n\util\ex\UnsupportedOperationException;

class GuiStructureDeclaration {
	private ?string $label = null;
	private ?string $helpText = null;
	private ?string $siStructureType = null;
	private ?GuiPropPath $guiFieldPath = null;
	private ?array $children = null;
	
	private function __construct() {
	}
	
	/**
	 * @return string|null
	 */
	function getLabel() {
		UnsupportedOperationException::assertTrue($this->guiFieldPath === null);
		
		return $this->label;
	}
	
	/**
	 * @return string|null
	 */
	function getHelpText() {
		UnsupportedOperationException::assertTrue($this->guiFieldPath === null);
		
		return $this->helpText;
	}
	
	/**
	 * @return string|null
	 */
	function getSiStructureType() {
		return $this->siStructureType;
	}
	
	function hasGuiFieldPath() {
		return $this->guiFieldPath !== null;
	}
	
	/**
	 * @return GuiPropPath
	 */
	function getGuiFieldPath() {
		UnsupportedOperationException::assertTrue($this->guiFieldPath !== null);
		
		return $this->guiFieldPath;
	}
	
	
	function hasChildrean() {
		return $this->children !== null;
	}
	
	/**
	 * @return GuiStructureDeclaration[]
	 */
	function getChildren() {
		UnsupportedOperationException::assertTrue($this->children !== null);
		
		return $this->children;
	}
	
	/**
	 * @param GuiStructureDeclaration $child
	 */
	function addChild(GuiStructureDeclaration $child) {
		UnsupportedOperationException::assertTrue($this->children !== null);
		
		$this->children[] = $child;
	}
	
	function getAllGuiFieldPaths() {
		if ($this->guiFieldPath !== null) {
			return [(string) $this->guiFieldPath => $this->guiFieldPath];
		}
		
		$guiFieldPaths = [];
		foreach ($this->children as $child) {
			$guiFieldPaths = array_merge($guiFieldPaths, $child->getAllGuiFieldPaths());
		}
		return $guiFieldPaths;
	}
	
	/**
	 * @param string $siStructureType
	 * @param GuiPropPath $guiFieldPath
	 * @param string|null $label
	 * @param string|null $helpText
	 * @return GuiStructureDeclaration
	 */
	static function createField(GuiPropPath $guiFieldPath, string $siStructureType) {
		$gsd = new GuiStructureDeclaration();
		$gsd->siStructureType = $siStructureType;
		$gsd->guiFieldPath = $guiFieldPath;
		return $gsd;
	}
	
	/**
	 * @param string $siStructureType
	 * @param GuiStructureDeclaration[] $children
	 * @param string|null $label
	 * @param string|null $helpText
	 * @return GuiStructureDeclaration
	 */
	static function createGroup(array $children, string $siStructureType, ?string $label, ?string $helpText = null) {
		ArgUtils::valArray($children, GuiStructureDeclaration::class);
		$gsd = new GuiStructureDeclaration();
		$gsd->siStructureType = $siStructureType;
		$gsd->children = $children;
		$gsd->label = $label;
		$gsd->helpText = $helpText;
		return $gsd;
	}
}

