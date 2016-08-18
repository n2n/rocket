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
namespace rocket\spec\config\extr;

class MenuItemExtraction {
	const ID_SPEC_MASK_DELIMITER = '&';
	
	private $id;
	private $moduleNamespace;
	private $specId;
	private $eiMaskId;
	private $label;
	
	private function __construct(string $id, string $moduleNamespace, string $specId, string $eiMaskId = null) {
		$this->id = $id;
		$this->moduleNamespace = $moduleNamespace;
		$this->specId = $specId;
		$this->eiMaskId = $eiMaskId;
	}
	
	public function getId(): string {
		return $this->id;
	}
	
	public function getModuleNamespace(): string {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace(string $moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	public function getSpecId(): string  {
		return $this->specId;
	}
	
	public function getEiMaskId() {
		return $this->eiMaskId;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function setLabel(string $label = null) {
		$this->label = $label;
	}
	
	public static function createFromId(string $menuItemId, string $moduleNamespace): MenuItemExtraction {
		$idParts = explode(self::ID_SPEC_MASK_DELIMITER, $menuItemId);
		
		switch (count($idParts)) {
			case 1:
				return new MenuItemExtraction($menuItemId, $moduleNamespace, $idParts[0]);
			case 2:
				return new MenuItemExtraction($menuItemId, $moduleNamespace, $idParts[0], $idParts[1]);
			default:
				throw new \InvalidArgumentException('Invalid id: ' . $menuItemId);
		}
	}
}
