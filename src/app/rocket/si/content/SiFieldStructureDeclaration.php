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
namespace rocket\si\content;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\ui\DisplayItem;

class SiFieldStructureDeclaration {
	private $siFieldDeclaration;
	private $displayType;
	private $children = [];
	
	/**
	 * @param SiFieldDeclaration $siFieldDeclaration
	 * @param string $label
	 */
	function __construct(SiFieldDeclaration $siFieldDeclaration, string $displayType) {
		$this->siFieldDeclaration = $siFieldDeclaration;
		$this->setDisplyType($displayType);
	}
	
	/**
	 * @return SiFieldDeclaration
	 */
	public function getSiFieldDeclaration() {
		return $this->siFieldDeclaration;
	}

	/**
	 * @param SiFieldDeclaration $siFieldId
	 * @return \rocket\si\content\SiFieldDeclaration
	 */
	public function setSiFieldDeclaration(SiFieldDeclaration $siFieldDeclaration) {
		$this->siFieldDeclaration = $siFieldDeclaration;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDisplayType() {
		return $this->displayType;
	}

	/**
	 * @param string $displayType
	 * @return \rocket\si\content\SiFieldDeclaration
	 */
	public function setDisplyType(string $displayType) {
		ArgUtils::valEnum($displayType, DisplayItem::getTypes());
		$this->displayType = $displayType;
		return $this;
	}
	
	/**
	 * @return SiFieldStructureDeclaration[]
	 */
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * @param SiFieldStructureDeclaration[] $children
	 */
	public function setChildren(array $children) {
		ArgUtils::valArray($children, SiFieldStructureDeclaration::class);
		$this->children = $children;
	}
}
